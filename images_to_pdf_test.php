<?php

$images = array("1661241c694efa40c016b48322ac9ebf-8.jpg", "1661241c694efa40c016b48322ac9ebf-9.jpg");

$pdf = new Imagick($images);
$pdf->setImageFormat('pdf');
$pdf->writeImages('combined.pdf', true);
