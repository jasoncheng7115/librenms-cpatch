<?php

$no_refresh = true;

$pagetitle[] = '搜尋';

$sections = array(
    'ipv4' => 'IPv4 位址',
    'ipv6' => 'IPv6 位址',
    'mac'  => 'MAC 位址',
    'arp'  => 'ARP 表',
    'fdb'  => 'FDB 表'
);

if (dbFetchCell('SELECT 1 from `packages` LIMIT 1')) {
    $sections['packages'] = '套件';
}

if (!isset($vars['search'])) {
    $vars['search'] = 'ipv4';
}

print_optionbar_start('', '');

echo '<span style="font-weight: bold;">搜尋</span> &#187; ';

unset($sep);
foreach ($sections as $type => $texttype) {
    echo $sep;
    if ($vars['search'] == $type) {
        echo "<span class='pagemenu-selected'>";
    }

    // echo('<a href="search/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $texttype .'</a>');
    echo generate_link($texttype, array('page' => 'search', 'search' => $type));

    if ($vars['search'] == $type) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

if (file_exists('pages/search/'.$vars['search'].'.inc.php')) {
    include 'pages/search/'.$vars['search'].'.inc.php';
} else {
    echo report_this('Unknown search type '.$vars['search']);
}
