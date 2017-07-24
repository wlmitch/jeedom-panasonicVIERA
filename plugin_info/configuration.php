<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../core/php/panasonicVIERAIptables.inc.php';
include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<script>
$(document).ready(function () {
    function updateIptablesFieldsVisibility() {
        if ($('#bt_iptables').prop('checked')) {
            $('#iptablesFields').show();
        } else {
            $('#iptablesFields').hide();
        }
    };

    $('#bt_iptables').change(function () {
        updateIptablesFieldsVisibility();
    });

    updateIptablesFieldsVisibility();
});
</script>
<form class="form-horizontal">
    <fieldset>
        <legend><i class="fa fa-list-alt"></i>  {{Général}}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Durée maximale d'execution des commandes (laisser vide par defaut)}}</label>
            <div class="col-lg-1">
                <input class="configKey form-control" data-l1key="command_timeout" placeholder="<?= panasonicVIERA::getConfigCommandTimeout() ?>"
                        type="number" min="0"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Adresse IP de broadcast pour les paquets WakeOnLan (laisser vide par defaut)}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="broadcast_ip" placeholder="<?= panasonicVIERA::getConfigBroadcastIp() ?>" type="text"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Durée maximale de la recherche des TVs (laisser vide par defaut)}}</label>
            <div class="col-lg-1">
                <input class="configKey form-control" data-l1key="discovery_timeout" placeholder="<?= panasonicVIERA::getConfigDiscoveryTimeout() ?>"
                        type="number" min="0"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Utiliser une règle Iptable spécifique durant la découverte des TVs}}</label>
            <div class="col-lg-1">
                <input type="checkbox" id="bt_iptables" class="configKey" data-l1key="discovery_iptables"/>
            </div>
        </div>

        <div id="iptablesFields">
            <legend><i class="fa fa-cog"></i>  {{Configurations Iptables}}</legend>
            <div class="alert alert-warning">
                {{Les paramètres ci-dessous définissent la règle qui sera appliquée lors de la découverte des TV sur le réseau.<br \/>Cette règle est conçut pour s'appliquer sur les paquets en entrée uniquement.<br \/>Veuillez à ne modifier les paramètres ci-dessous que si vous savez ce que vous faite.}}
            </div>
<?php foreach (panasonicVIERAIptables::getIptablesSettings() as $name => $setting) {
            if (isset($setting['visible']) && !$setting['visible']) {
                continue;
            }
?>
            <div class="form-group">
                <?php if (isset($setting['note']) && !empty($setting['note'])) : ?>
                <label class="col-md-3 control-label" data-toggle="tooltip" data-placement="top" title="<?= __($setting['note'], __FILE__) ?>"><?= __($setting['description'], __FILE__) ?></label>
                <?php else : ?>
                <label class="col-md-3 control-label"><?= __($setting['description'], __FILE__) ?></label>
                <?php endif; ?>
                <div class="col-md-5">
                    <input class="configKey form-control" type="text" data-l1key="discovery_iptables_settings_<?= $name ?>"
                        placeholder="<?= panasonicVIERAIptables::getConfigDiscoveryIptablesSettings($name) ?>">
                </div>
            </div>
<?php } ?>
        </div>
    </fieldset>
</form>
<br />
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <a data-toggle="collapse" href="#collapse1">{{Faire un don}}</a>
        </div>
        <div id="collapse1" class="panel-collapse collapse">
            <div class="panel-body">
                {{Ce plugin est gratuit afin d'être accessible à tout le monde facilement. Si vous le souhaitez vous pouvez faire une donation au développeur via le lien suivant}}
                <br />
                <br />
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="HC5NXE3C7Y7AW">
        <?php switch(translate::getLanguage()) :
            case 'fr_FR': ?>
                    <input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="{{Faire un don via Paypal}}">
        <?php   break; ?>
        <?php default: ?>
                    <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/i/btn/png/btn_donate_92x26.png" border="0" name="submit" alt="{{Faire un don via Paypal}}">
        <?php   break; ?>
        <?php endswitch; ?>
                    <img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
                </form>
            </div>
        </div>
    </div>
</div>
