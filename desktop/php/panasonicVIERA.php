<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'panasonicVIERA');
$eqLogics = eqLogic::byType('panasonicVIERA');
?>
<div id="div_scanAlert">
</div>
<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter une TV}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
<?php foreach ($eqLogics as $eqLogic) : ?>
                <li class="cursor li_eqLogic" data-eqLogic_id="<?= $eqLogic->getId() ?>"><a><?= $eqLogic->getHumanName(true) ?></a></li>
<?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
         		<center>
           			<i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
         		</center>
         		<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
       		</div>
     	</div>

        <legend>{{Mes Télévisions}}
        </legend>

        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
                <center>
                    <i class="fa fa-plus-circle" style="font-size : 7em;color:#28a3d3;"></i>
                </center>
                <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#28a3d3"><center>{{Ajouter}}</center></span>
            </div>
<?php foreach ($eqLogics as $eqLogic) : $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive'); ?>
            <div class="eqLogicDisplayCard cursor" data-eqLogic_id="<?= $eqLogic->getId() ?>" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
                <center>
                    <img src="plugins/panasonicVIERA/plugin_info/panasonicVIERA_icon.png" height="105" width="95" />
            	</center>
            	<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center><?= $eqLogic->getHumanName(true, true) ?></center></span>
            </div>
<?php endforeach; ?>
        </div>
    </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
        <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
            <li role="presentation" class="expertModeVisible"><a href="#featuretab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-id-card-o"></i> {{Caractéristiques}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>
        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <form class="form-horizontal">
                    <fieldset>
                        <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Nom de la TV}}</label>
                            <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                            <div class="col-sm-3">
                                <div id="div_inputGroupName" class="">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{TV's name}}"/>
                                    <span id="span_setName" class="input-group-btn" title="Remplir le nom automatiquement via la valeur récupérée de la TV" style="display : none;"><a id="bt_setName" class="btn btn-default btn-success"><i class="fa fa-magic"></i></a></span>
                                </div>
                            </div>
                            <label class="col-sm-3 control-label expertModeVisible">{{Modèle}}</label>
                            <div class="col-lg-2 col-sm-5 expertModeVisible">
                                <span class="eqLogicAttr label label-primary" data-l1key="configuration" data-l2key="<?= panasonicVIERA::KEY_MODEL ?>" style="font-size : 1em;"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                            <div class="col-sm-3">
                            	<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                	<option value="">{{Aucun}}</option>
    <?php foreach (object::all() as $object) : ?>
                        		<option value="<?= $object->getId() ?>"><?= $object->getName() ?></option>
    <?php endforeach; ?>
                            	</select>
                        	</div>
                   		</div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Categorie de la TV}}</label>
                            <div class="col-sm-8 col-lg-8">
    <?php foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) : ?>
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="<?= $key ?>" /><?= $value['name'] ?>
                                </label>
    <?php endforeach; ?>
                            </div>
                        </div>
                   	    <div class="form-group">
                            <label class="col-sm-3 control-label" >{{Actif}}</label>
                            <div class="col-sm-9">
                                <label class="checkbox-inline">
                                    <input class="eqLogicAttr" data-l1key="isEnable" checked="" type="checkbox">{{Actif}}
                                </label>
                                <label class="checkbox-inline">
                                    <input class="eqLogicAttr" data-l1key="isVisible" checked="" type="checkbox">{{Visible}}
                                </label>
                            </div>
               			</div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Adresse IP}}</label>
                            <div class="col-lg-2 col-sm-5">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= panasonicVIERA::KEY_ADDRESS ?>" placeholder="{{IP Address}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" >{{Affichage des groupes de commandes}}</label>
                            <div class="col-sm-9">
    <?php foreach (panasonicVIERA::COMMANDS_GROUPS as $key => $name) : ?>
                                <label class="checkbox-inline">
                                    <input class="eqLogicAttr" data-l1key="configuration" data-l2key="<?= $key ?>" checked="" type="checkbox"><?= __($name, __FILE__) ?>
                                </label>
    <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Couleur des commandes}}</label>
                            <div class="col-lg-2 col-sm-2">
                                <select class="eqLogicAttr form-control" data-l1key='configuration' data-l2key='<?= panasonicVIERA::KEY_THEME ?>'>
                                    <option value='white'>{{Bouton blancs}}</option>
                                    <option value='black'>{{Bouton noirs}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Pas de modification du volume}}</label>
                            <div class="col-lg-1 col-sm-1">
                                <select class="eqLogicAttr form-control" data-l1key='configuration' data-l2key='<?= panasonicVIERA::KEY_VOLUMESTEP ?>'>
                                    <option value=1>{{1}}</option>
                                    <option value=2>{{2}}</option>
                                    <option value=3>{{3}}</option>
                                    <option value=4>{{4}}</option>
                                    <option value=5>{{5}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group expertModeVisible">
                            <label class="col-sm-3 control-label" >{{Remonter les erreurs d'execution des commandes}}</label>
                            <div class="col-sm-9">
                                <label class="checkbox-inline"><input class="eqLogicAttr" data-l1key="configuration" data-l2key="<?= panasonicVIERA::KEY_TRIGGER_ERRORS ?>" type="checkbox">
                                </label>
                            </div>
               			</div>
                    </fieldset>
                </form>
            </div>
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Commandes}}</legend>
                <div class="alert alert-info">
                    {{Info : <br /> Les commandes ci-dessous sont ajoutées en fonction des groupes activés de l'équipement}}
                </div>
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th style="width: 300px;">{{Nom}}</th>
                            <th>{{Description}}</th>
                            <th class="expertModeVisible">{{Type}}</th>
                            <th class="expertModeVisible">{{Id logique}}</th>
                            <th>{{Paramètre(s)}}</th>
                            <th>{{Actions}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_file('desktop', 'panasonicVIERA', 'js', 'panasonicVIERA');?>
<?php include_file('core', 'plugin.template', 'js');?>
