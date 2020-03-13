<?php declare(strict_types = 1);

require 'vendor/autoload.php';

use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadFromUrl('https://kino.napajedla.cz/cs/program');
$programs = $dom->find('.novinka-kalendar');

$programs = array_map(function ($program) {
	/** @var Dom\HtmlNode $program */
	$title = replace($program->find('h3')->find('a')[0]->text());
	$desc = replace( $program->find('.popis')[0]->text());
	$time = $program->find('.cas')[0]->text();
	list(, $date) = explode(',', $program->find('.datum')[0]->text());

	$premiere = $program->find('.col1')[0] ? $program->find('.col1')[0]->text() : '';

	$price = replace( $program->find('.col2')[0]->text());
	preg_match('!\d+!', trim(replace($program->find('.col3')[0]->text())), $movieTime);
	$movieTime = $movieTime[0];

	list($day, $month, $year) = explode('.', trim($date));

	$monthMinusOne = $month - 1;
	$start = DateTime::createFromFormat('Y-m-d H:i', "$year-$monthMinusOne-$day $time");
	$end = clone $start;

	$end->add(new DateInterval('PT' . $movieTime . 'M'));

	$desc = html_entity_decode($desc . "\n\n$premiere $price, $movieTime min");

	return [
		'title' => $title,
		'desc' => $desc,
		'start' => $start->format(DateTimeInterface::ISO8601),
		'end' => $end->format(DateTimeInterface::ISO8601),
	];
}, $programs->toArray());

function replace(string $string): string {
	return str_replace('&amp;', '&', $string);
}

header('Content-Type: application/json');
echo json_encode($programs);
