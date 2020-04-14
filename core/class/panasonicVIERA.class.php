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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';


class panasonicVIERA extends eqLogic {

    // name of ip address configuration key
    const KEY_ADDRESS = 'address';
    const KEY_APP_ID = 'app_id';
    const KEY_ENC_KEY = 'enc_key';
    // name of uuid configuration key
    const KEY_UUID = 'uuid';
    // name of the features configuration key
    const KEY_FEATURES = 'features';
    // configure the increase steps of volumes actions
    const KEY_VOLUMESTEP = 'volume_step';
    // configure the color of buttons
    const KEY_THEME = 'theme';
    // this settings allow errors of commands to be triggered
    const KEY_TRIGGER_ERRORS = 'trigger_errors';

    // list of commands groups with full name
    const COMMANDS_GROUPS = [
        'basic' => 'Basiques',
        'numeric' => 'Numeriques',
        'record' => 'Enregistrement',
        'multimedia' => 'Multimedia',
        'colors' => 'Couleurs',
        'others' => 'Autres'
    ];

    // The mapping of erros messages with errors codes
    const PANASONIC_VIERA_LIB_ERRORS = [
        408 => 'La TV est indisponible',
        405 => 'Cette commande semble ne pas être supportée par la TV'
    ];

    /*     * *************************Attributs****************************** */
    /**
     * @var this is the official commands index
     */
    protected static $_command_index = [];


    /*     * ***********************Methode static*************************** */
    /**
     * Function that compare two command object
     *
     * @param $cmd_a
     * @param $cmd_b
     * @return [int]
     *
     */
    public static function sortListCmd($cmd_a, $cmd_b) {
        if ($cmd_a['name'] == $cmd_b['name']) {
            return 0;
        }
        return ($cmd_a['name'] < $cmd_b['name']) ? -1 : 1;
    }

    /**
     * Return the list of official commands
     *
     * @return array
     */
    public static function getCommandsIndex() {
        if (empty(self::$_command_index)) {
            self::$_command_index = include(__DIR__ . '/../config/commands.config.php');
            usort(self::$_command_index, array('panasonicVIERA', 'sortListCmd'));
        }
        return self::$_command_index;
    }

    /**
     * Retourne les groupes de l'objet
     *
     * @var array
     */
    public static function getCommandGroups() {
        return self::COMMANDS_GROUPS;
    }

    /**
     * Return the dependency info about this plugin
     *
     * @return [array] an array with the following keys
     *    log : the name of the log file
     *    progress_file : the path to the file which indicates the progres status
     *    state : the status of dependancies
     */
    public static function dependancy_info() {
        $return = array();
        $return['log'] = 'panasonicVIERA_dependency';
        $return['progress_file'] = '/tmp/dependency_panasonicVIERA_in_progress';
        $return['state'] = 'ok';
        return $return;
    }

    /**
     * Run the installation of dependancies
     *
     */
    public static function dependancy_install() {
        log::remove('panasonicVIERA_dependency');
        $cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../resources/install.sh';
        $cmd .= ' >> ' . log::getPathToLog('panasonicVIERA_dependency') . ' 2>&1 &';
        exec($cmd);
    }

    /**
     * Execute a 3rd party command not written in PHP
     *
     * @param [string] the name of the command file in 3rdparty/ directory
     * @param [array] the list of command arguments
     * @param [string] OPTIONNAL a verbose name to include in errors statments.
     * @param [boolean] OPTIONNAL if true 3rd party command errors will be threw as exception.
     * @return mixed : the output of the command (stdout of the command)
     * @throw Exception in case of failure
     */
    public static function execute3rdParty($command, $args = [], $name = null, $throw_errors = true, $error_codes = []) {
        $base_path = realpath(__DIR__ . '/../../3rdparty');
        $extension = pathinfo($command, PATHINFO_EXTENSION);
        $runtime = 'bash';
        switch ($extension) {
            case 'py':
                $runtime = 'python';
                break;
        }

        $cmdline = sprintf("%s %s/%s %s", $runtime, $base_path, $command, implode(' ', $args));
        // by default the log 'name' will be the command name
        if ($name === null) {
            $name = $command;
        }

        $output = null;

        log::add('panasonicVIERA', 'debug', 'execute3rdParty : '. $cmdline);
        $shell_output = trim(shell_exec(escapeshellcmd($cmdline)));
        if ($shell_output == 'null') {
            log::add('panasonicVIERA', 'debug', "execute3rdParty : command $command has returned null");
            return null;
        }

        // decode json raw output
        $decoded = json_decode($shell_output, JSON_OBJECT_AS_ARRAY|JSON_NUMERIC_CHECK);
        if (is_null($decoded)) {
            log::add('panasonicVIERA', 'debug', "execute3rdParty : $command's output : $shell_output");
            throw new Exception(__("La commande", __FILE__) . " $command " . __('n\'a pas retournée de données JSON valides.', __FILE__));
        }

        # transcript logs messages from python script to jeedom
        if (isset($decoded['log'])) {
            foreach ($decoded['log'] as $record) {
                log::add('panasonicVIERA', $record['level'], $record['message']);
            }
        }
        # handle return code and error message
        if (isset($decoded['status']) && intval($decoded['status']) != 0) {
            $message = __("La commande", __FILE__) . " $name " . __('a echouée.', __FILE__);
            if ( isset($decoded['error_code']) ) {
                log::add('panasonicVIERA', 'debug', "execute3rdParty : command $command has returned error code : " . $decoded['error_code']);
            }
            if ( isset($decoded['error_code']) && isset($error_codes[$decoded['error_code']]) ) {
                $message .= "<br />" . __($error_codes[$decoded['error_code']], __FILE__);
            } elseif (isset($decoded['error'])) {
                $message .= "<br />" . __($decoded['error'], __FILE__);
            }
            if ($throw_errors) {
                throw new Exception($message);
            }
        }

        # handle standard output
        if (isset($decoded['output'])) {
            $output = $decoded['output'];
        }

        return $output;
    }


    /*     * *********************Méthodes d'instance************************* */

    /**
     * Ajoute une commande à l'objet
     *
     * @param cmd $cmd La commande a ajouter
     */
    protected function addCommand($command, $update = false) {
        if (!is_array($command)) {
            return;
        }

        $panasonicVIERACmd = cmd::byEqLogicIdCmdName($this->getId(), $command['name']);
        if ( is_object($panasonicVIERACmd) && !$update ) {
            log::add('panasonicVIERA', 'debug', '=> addCommand('. $command['name'].') command already exist');
            return;
        }
        if (!is_object($panasonicVIERACmd)) {
            log::add('panasonicVIERA', 'debug', '=> addCommand('. $command['name'].') add command');
            $panasonicVIERACmd = new panasonicVIERACmd();
            $panasonicVIERACmd->setEqLogic_id($this->getId());
        } else {
            log::add('panasonicVIERA', 'debug', '=> addCommand('. $command['name'].') update command');
        }

        $panasonicVIERACmd->setName($command['name']);
        $panasonicVIERACmd->setLogicalId(isset($command['logicalId']) ? $command['logicalId'] : $command['configuration']['command']);
        foreach ($command['configuration'] as $key => $value) {
            $panasonicVIERACmd->setConfiguration($key, $value);
        }
        $panasonicVIERACmd->setType($command['type']);
        $panasonicVIERACmd->setSubType($command['subType']);
        if (isset($command['icon']) && $command['icon'] != '') {
            $panasonicVIERACmd->setDisplay('icon', '<i class=" '.$command['icon'].'"></i>');
        }
        $panasonicVIERACmd->save();
    }

    /**
     * Supprime la commande $name de l'objet
     *
     * @param String $name Le nom de la commande
     */
    protected function removeCommand($cmd) {
        if (($panasonicVIERACmd = cmd::byEqLogicIdCmdName($this->getId(), $cmd['name']))) {
            log::add('panasonicVIERA', 'debug', '=> removeCommand('. $cmd['name'].') remove command');
            $panasonicVIERACmd->remove();
        }
    }

    /**
     * Ajoute un groupe de commandes
     *
     * @param String $groupName Le nom du groupe de commandes
     */
    protected function addCommands($group_name) {
        log::add('panasonicVIERA', 'debug', '=> addCommands('.$group_name.')');

        foreach (self::getCommandsIndex() as $cmd) {
            # TODO remove filter on infos commands
            if ($cmd['configuration']['group'] == $group_name && $cmd['type'] == 'action' && $cmd['configuration']['action'] == 'sendkey') {
                $this->addCommand($cmd);
            }
        }
    }

    /**
     * Supprime un groupe de commandes
     *
     * @param String $groupName Le nom du groupe de commandes
     */
    protected function removeCommands($group_name) {
        log::add('panasonicVIERA', 'debug', '=> removeCommands('.$group_name.')');
        foreach (self::getCommandsIndex() as $cmd) {
            if ($cmd['configuration']['group'] == $group_name) {
                $this->removeCommand($cmd);
            }
        }
    }

    /*    Data manipulation function    */

    public function preInsert() {
        $this->setConfiguration(self::KEY_TRIGGER_ERRORS, false);
        $this->setConfiguration(self::KEY_VOLUMESTEP, 2);
        $this->setConfiguration(self::KEY_THEME, 'white');
    }

    public function postInsert() {

    }

    public function preUpdate() {
        $addr = $this->getConfiguration(self::KEY_ADDRESS);
        if ($addr == '') {
            log::add('panasonicVIERA', 'debug', '=> preUpdate: ip address empty');
            throw new Exception(__('L\'adresse IP ne peut etre vide. Vous pouvez la trouver dans les paramètres de votre TV ou de votre routeur (box).', __FILE__));
        }
        $this->setIpAddress($addr);
    }

    public function postUpdate() {

    }

    public function preSave() {
        if (!$this->getId()) {
            log::add('panasonicVIERA', 'debug', '=> preSave empty id');
            return;
        }

        log::add('panasonicVIERA', 'debug', 'official index contains : ' . count(self::getCommandsIndex()). " commands");

        foreach(self::getCommandGroups() as $key => $name) {
            if ($this->getConfiguration($key) == 1) {
                log::add('panasonicVIERA', 'debug', "add $name commands");
                $this->addCommands($key);
            } else {
                log::add('panasonicVIERA', 'debug', "remove $name commands");
                $this->removeCommands($key);
            }
        }
    }

    public function postSave() {

    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version, [], true);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        log::add('panasonicVIERA', 'debug', sprintf('=> toHtml: ask widget for %s version', $version));

        # prepare the replacement for all #cmd# keys
        $cmds_replace = [];
        foreach ($this->getCmd() as $cmd) {
            $cmd_html = ' ';
            $group = $cmd->getConfiguration('group');
            if ($cmd->getIsVisible()) {
                if ($cmd->getType() == 'info') {
                    // info commands
                    $cmd_html = $cmd->toHtml();
                } else {
                    $vcolor = ($version == 'mobile') ? 'mcmdColor' : 'cmdColor';
                    if ($this->getPrimaryCategory() == '') {
                        $cmdColor = jeedom::getConfiguration('eqLogic:category:default:' . $vcolor);
                    } else {
                        $cmdColor = jeedom::getConfiguration('eqLogic:category:' . $this->getPrimaryCategory() . ':' . $vcolor);
                    }
                    // action commands
                    $cmd_replace = array(
                        '#id#'           => $cmd->getId(),
                        '#name#'         => $cmd->getName(),
	                    '#name_display#' => ($cmd->getDisplay('icon') != '') ? $cmd->getDisplay('icon') : $cmd->getName(),
                        '#theme#'        => $this->getConfiguration(self::KEY_THEME),
                        '#version#'      => $version,
                        '#uid#'          => 'cmd' . $cmd->getId() . eqLogic::UIDDELIMITER . mt_rand() . eqLogic::UIDDELIMITER,
                        '#cmdColor#'     => $cmdColor
                    );

                    #$cmd_html = template_replace($cmd_replace, getTemplate('core', $version, 'cmd.action.other.default'));
                    $cmd_html = template_replace($cmd_replace, getTemplate('core', $version, 'cmd', 'panasonicVIERA'));
                }
            }
            if ( ! isset($groups_templates[$group]) ) {
                $groups_templates[$group] = '';
            }
            $cmds_replace[sprintf( '#%s#', strtolower($cmd->getName()) )] = $cmd_html;
        }

        # dump list of ## keys
        #log::add('panasonicVIERA','debug', implode(' ', array_keys($cmds_replace)));

        // Generate template for groups used in commands
        foreach ($groups_templates as $group => $html) {
            $group_template = getTemplate('core', $version, $group, 'panasonicVIERA');
            $replace[sprintf('#group_%s#', $group)] = template_replace($cmds_replace, $group_template);
        }

        // Generate template for groups not used in commands
        foreach ($this->getCommandGroups() as $group => $name) {
            if ( ! isset($groups_templates[$group]) ) {
                $replace[sprintf('#group_%s#', $group)] = '';
            }
        }

        $parameters = $this->getDisplay('parameters');
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $replace['#' . $key . '#'] = $value;
            }
        }

        return template_replace($replace, getTemplate('core', $version, 'eqLogic', 'panasonicVIERA'));
    }

    /*     * **********************Getteur Setteur*************************** */

    /**
     * Get the IP address of this eq
     *
     * @return string|null the IP address if available
     */
    public function getIpAddress() {
        return $this->getConfiguration(panasonicVIERA::KEY_ADDRESS);
    }

    /**
     * Set the new ip address for this command
     *
     * @param string the new IP address
     * @return this
     * @throw Exception if ip address is not valid
     */
    public function setIpAddress($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            log::add('panasonicVIERA', 'debug', '=> setIpAddress: ip address checking failure');
            throw new Exception(__('Vous avez saisit une mauvaise adresse IP', __FILE__). " '$ip'.");
        }
        $this->setConfiguration(panasonicVIERA::KEY_ADDRESS, $ip);
        return $this;
    }

}

class panasonicVIERACmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */
    public function execute($_options = array()) {
        $panasonicTV = $this->getEqLogic();
        $tvip = $panasonicTV->getIpAddress();

        switch($this->getType()) {
            case 'action':
                $action = $this->getConfiguration('action');
                $command = $this->getConfiguration('command');
                if (empty($action) || is_null($action)) {
                    throw new Exception('Tried to execute a command with an empty action');
                }
                if (empty($command) || is_null($command)) {
                    throw new Exception('Tried to execute a command with an empty command');
                }
                log::add('panasonicVIERA', 'debug', sprintf('execute: Action command : %s', $action));
                $result = panasonicVIERA::execute3rdParty("panasonic_viera_adapter.py",
                        ['--timeout', panasonicVIERA::getConfigCommandTimeout(), $action, $tvip, $command],
                        $this->getName(),
                        ($panasonicTV->getConfiguration(panasonicVIERA::KEY_TRIGGER_ERRORS, false) == true ? true : false),
                        panasonicVIERA::PANASONIC_VIERA_LIB_ERRORS);
                if (is_null($result)) {
                    throw new Exception(__('La commande a retournée une valeur nulle, veuillez vérifier les dépendances et les log', __FILE__));
                }
                break;
            case 'info':
                log::add('panasonicVIERA', 'debug', 'Info command');
                $action = $this->getConfiguration('action');
                $command = $this->getConfiguration('command');
                return panasonicVIERA::execute3rdParty("panasonic_viera_adapter.py",
                        ['--timeout', panasonicVIERA::getConfigCommandTimeout(), $action, $tvip, $command],
                        $this->getName(),
                        ($panasonicTV->getConfiguration(panasonicVIERA::KEY_TRIGGER_ERRORS, false) == true ? true : false),
                        panasonicVIERA::PANASONIC_VIERA_LIB_ERRORS);
            default:
                throw new Exception(sprintf('Tried to execute an unknown command type : %s', $this->getType()));
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
