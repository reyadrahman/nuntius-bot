<?php

require_once 'vendor/autoload.php';

Kint::dump(\Nuntius\Nuntius::getFbPostBackManager()->getPostBack('toss_a_coin'));
