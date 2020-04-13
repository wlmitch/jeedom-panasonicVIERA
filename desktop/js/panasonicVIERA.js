
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

/*
 * GLOBAL VIEW
 */

/*
 * EQUIPMENT VIEW
 */

function printEqLogic(_eqLogic) {
    actionOptions = []
    $('#table_features tbody').empty();
    if (isset(_eqLogic.configuration)) {
        if (isset(_eqLogic.configuration.features)) {
            for (var feat in _eqLogic.configuration.features) {
                addFeature(feat, _eqLogic.configuration.features[feat]);
            }
        }

        if (isset(_eqLogic.configuration.features) && isset(_eqLogic.configuration.features.name)) {
            $('#div_inputGroupName').addClass('input-group');
            $('#span_setName').show();
            $('#bt_setName').on('click', function () {
                $('.eqLogicAttr[data-l1key=name]').value(_eqLogic.configuration.features.name);
            });
        } else {
            $('#div_inputGroupName').removeClass('input-group');
            $('#span_setName').hide();
        }
    }
}

function addFeature(name, value) {
    name = name.charAt(0).toUpperCase() + name.replace('_', ' ').slice(1);
    var tr = '<tr>';
    tr += '<td>';
    tr += name;
    tr += '</td>';
    tr += '<td>';
    tr += value;
    tr += '</td>';
    tr += '</tr>';
    $('#table_features tbody').append(tr);

//    <label class="col-sm-3 control-label">{{ ${name} }}</label>
//    <div class="col-lg-2 col-sm-5">
//        <span class="label label-primary" style="font-size : 1em;">${value}</span>
//    </div>
//</div>
}

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var disable_edit = false;
    var disabled_attr = '';
    if (isset(_cmd.configuration.autocreated) && _cmd.configuration.autocreated) {
        disable_edit = true;
        disabled_attr = 'disabled';
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<div class="row">';
    tr += '<div class="col-sm-6">';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</div>';
    tr += '<div class="col-sm-6">';
    tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icône</a>';
    tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
    tr += '</div>';
    tr += '</div>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input disabled="" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="description" style="width : 140px;">';
    tr += '</td>';
    tr += '<td class="expertModeVisible">';
    if (disable_edit) {
        tr += '<input class="form-control input-sm" disabled style="width : 120px;" value="'+ _cmd.type + '">';
        tr += '<input class="form-control input-sm" disabled style="width : 120px;" value="'+ _cmd.subType + '">';
    } else {
        tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    }
    tr += '</td>';
    tr += '<td class="expertModeVisible"><input class="cmdAttr form-control input-sm" ' + disabled_attr +' data-l1key="logicalId" style="width : 70%; display : inline-block;" placeholder="{{Commande key}}"><br/>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input class="cmdAttr checkbox-inline" data-l1key="isVisible" checked="" type="checkbox">{{Afficher}}</label></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (!disable_edit) {
        if (isset(_cmd.type)) {
            $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
    }
}
