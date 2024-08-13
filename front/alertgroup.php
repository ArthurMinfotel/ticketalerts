<?php

include('../../../inc/includes.php');

Html::header(PluginTicketalertsAlertGroup::getTypeName(2), '', "admin", "pluginticketalertsalertgroup", "alertgroup");

$alert = new PluginTicketalertsAlert();

if ($alert->canView() || Session::haveRight("config", UPDATE)) {
   Search::show('PluginTicketalertsAlertGroup');

} else {
   Html::displayRightError();
}

Html::footer();
