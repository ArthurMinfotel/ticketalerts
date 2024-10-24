<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ticketalerts plugin for GLPI
 Copyright (C) 2003-2016 by the Ticketalerts Development Team.

 https://github.com/InfotelGLPI/ticketalerts
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Ticketalerts.

 ticketalerts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 ticketalerts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ticketalerts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginTicketalertsAlertGroupCriteria extends CommonDBTM {

    const MOVE_BEFORE = 'before';
    const MOVE_AFTER = 'after';
    
    public $dohistory = true;
    static $rightname = 'plugin_ticketalerts_manage_groupalert';

    static $criterias_types = [
        '',
        'itilcategory',
        'alert_type',
        'ticket_status',
        'ticket_type',
        'ticket_impact',
        'ticket_priority',
        'ticket_urgency',
        'ticket_location',
        'ticket_creator',
        'alert_creator',
        'ticket_source',
        'entity'
    ];

    static $recursive_criterias = [
        'itilcategory',
        'entity',
        'ticket_location'
    ];

    /**
     * @param int $nb
     *
     * @return string|\translated
     */
    static function getTypeName($nb = 0) {
        return _n('Criterion', 'Criteria', $nb, 'ticketalerts');
    }

    /**
     * Get menu name
     *
     * @return string
     */
    static function getMenuName() {
        return self::getTypeName();
    }

    static function canDelete() {
        return Session::haveRight(PluginTicketalertsAlertGroupCriteria::$rightname, READ);
    }


    static function canCreate() {
        return Session::haveRight(PluginTicketalertsAlertGroupCriteria::$rightname, READ);
    }

    /**
     * @param array|\datas $input
     *
     * @return array|\datas|\the
     */
    function prepareInputForAdd($input)
    {
            if (empty($input["criteria"])) {
                Session::addMessageAfterRedirect(__("Please select a criteria before submit", 'ticketalerts'),
                    false, ERROR);
                return false;
            }
        return $input;
    }

    static function getRuleCriterionValueByCriterion($input) {
        switch ($input['rule_criterion']) {
            case 'AND':
                return __('AND');
                break;
            case 'OR':
                return __('OR');
                break;
            default:
                break;
        }
    }


    static function getFilterValueByCriterion($input) {
        switch ($input['criteria']) {
            case 'ticket_type':
                return Ticket::getTicketTypeName($input['filter']);
            case 'itilcategory':
                $itil_cat = new ITILCategory();
                $itil_cat->getFromDB($input['filter']);
                return $itil_cat->getField('name');
            case 'alert_type':
                $alert_type = new PluginTicketalertsAlertType();
                $alert_type->getFromDB($input['filter']);
                return $alert_type->getField('name');
            case 'ticket_status':
                return Ticket::getStatus($input['filter']);
            case 'ticket_impact':
                return CommonITILObject::getImpactName($input['filter']);
            case 'ticket_urgency':
                return CommonITILObject::getUrgencyName($input['filter']);
            case 'ticket_priority':
                return CommonITILObject::getPriorityName($input['filter']);
            case 'ticket_location':
                $location = new Location();
                $location->getFromDB($input['filter']);
                return $location->getField('name');
            case 'ticket_creator':
            case 'alert_creator':
                return getUserName($input['filter']);
            case 'ticket_source':
                $requesttype = new RequestType();
                $requesttype->getFromDB($input['filter']);
                return $requesttype->getField('name');
            case 'entity':
                $entity = new Entity();
                $entity->getFromDB($input['filter']);
                return $entity->getRawCompleteName();
        }
        return '';
    }

    /**
     * Get the SQL selector for the corresponding field in database
     * @param $input string
     * @return string|void format = `table`.`column`
     */
    static function giveWhereClauseByCriteria($input) {

        switch ($input) {
            case 'ticket_type':
                return '`glpi_tickets`.`type`';
                break;
            case 'itilcategory':
                return '`glpi_tickets`.`itilcategories_id`';
                break;
            case 'alert_type':
                return '`glpi_plugin_ticketalerts_alerts`.`plugin_ticketalerts_alerttypes_id`';
                break;
            case 'ticket_status':
                return '`glpi_tickets`.`status`';
                break;
            case 'ticket_impact':
                return '`glpi_tickets`.`impact`';
                break;
            case 'ticket_urgency':
                return '`glpi_tickets`.`urgency`';
                break;
            case 'ticket_location':
                return '`glpi_tickets`.`locations_id`';
                break;
            case 'ticket_creator':
                return '`glpi_tickets`.`users_id_recipient`';
                break;
            case 'alert_creator':
                return '`glpi_plugin_ticketalerts_alerts`.`users_id_requester`';
                break;
            case 'ticket_source':
                return '`glpi_tickets`.`requesttypes_id`';
            case 'entity':
                return '`glpi_tickets`.`entities_id`';
                break;
            default:
                break;
        }
    }

    /**
     * Get the standard massive actions which are forbidden
     *
     * @since version 0.84
     *
     * @return an array of massive actions
     **/
    public function getForbiddenStandardMassiveAction() {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    static function getCriteriasNameForDropdown($values) {
        $tabs = [];

        foreach ($values as $value) {
            switch ($value) {
                case 'itilcategory':
                    $tabs[$value] = __('Ticket category','ticketalerts');
                    break;
                case 'alert_type':
                    $tabs[$value] = PluginTicketalertsAlertType::getTypeName();
                    break;
                case 'ticket_status':
                    $tabs[$value] = __('Status');
                    break;
                case 'ticket_type':
                    $tabs[$value] = __('Type');
                    break;
                case 'ticket_impact':
                    $tabs[$value] = __('Impact');
                    break;
                case 'ticket_urgency':
                    $tabs[$value] = __('Urgency');
                    break;
                case 'ticket_priority':
                    $tabs[$value] = __('Priority');
                    break;
                case 'ticket_location':
                    $tabs[$value] = __('Location');
                    break;
                case 'ticket_creator':
                    $tabs[$value] = __('Ticket creator','ticketalerts');
                    break;
                case 'alert_creator':
                    $tabs[$value] = __('Ticket alert creator','ticketalerts');
                    break;
                case 'ticket_source':
                    $tabs[$value] = __('Request source');
                    break;

                case 'entity':
                    $tabs[$value] = Entity::getTypeName();
                    break;
                default:
                    $tabs[$value] =Dropdown::EMPTY_VALUE;
                    break;
            }
        }
        ksort($tabs);

        return $tabs;
    }

    static function getArrayCriterionNegation() {
        $tab = ['AFFIRMATION' =>__('is', 'ticketalerts'),
            'NEGATION' => __('is not', 'ticketalerts'),
        ];

        return $tab;
    }

    static function getValueOfNegation($key) {
        $tab = ['AFFIRMATION' =>__('is', 'ticketalerts'),
            'NEGATION' => __('is not', 'ticketalerts'),
        ];

        return $tab[$key];
    }

    /**
     * Update groups_operator for the criteria's alertgroup
     * @return void
     */
    public function post_addItem()
    {
        $criterias = $this->find(
            ['plugin_ticketalerts_alertgroups_id' => $this->fields['plugin_ticketalerts_alertgroups_id']],
            ['`group_number` ASC, `rank` ASC']
        );
        $group = new PluginTicketalertsAlertGroup();
        $group->getFromDB($this->fields['plugin_ticketalerts_alertgroups_id']);
        $groupsOperators = $group->getGroupsOperatorsArray($criterias);
        $group->update([
            'id' => $group->getID(),
            'groups_operators' => json_encode($groupsOperators)
        ]);
    }

    public function post_purgeItem()
    {
        $this->post_addItem();
    }

    public function post_deleteItem()
    {
        $this->post_addItem();
    }

    /**
     * See RuleCollection::moveRule() (10.0.16)
     * slightly modified
     * @param $ID
     * @param $ref_ID
     * @param $group_number
     * @param $type
     * @return bool
     */
    static function moveCriteria($ID, $ref_ID, $group_number, $type = self::MOVE_AFTER) {
        /** @var \DBmysql $DB */
        global $DB;
        $criteria = new self();

        // Get actual ranking of criteria to move
        $criteria->getFromDB($ID);
        $old_rank = $criteria->fields["rank"];
        $alertgroups_id = $criteria->fields['plugin_ticketalerts_alertgroups_id'];

        // Compute new ranking
        if ($ref_ID) { // Move after/before an existing rule
            $criteria->getFromDB($ref_ID);
            $rank = $criteria->fields["rank"];
        } else if ($type == self::MOVE_AFTER) {
            // Move after all
            $result = $DB->request([
                'SELECT' => ['MAX' => 'rank AS maxi'],
                'FROM'   => $criteria->getTable(),
                'WHERE'  => ['group_number' => $group_number]
            ])->current();
            $rank   = $result['maxi'];
        } else {
            // Move before all
            $rank = 1;
        }

        $result = false;

        if ($old_rank < $rank) {
            if ($type == self::MOVE_BEFORE) {
                $rank--;
            }

            // Move back all rules between old and new rank
            $iterator = $DB->request([
                'SELECT' => ['id', 'rank'],
                'FROM'   => $criteria->getTable(),
                'WHERE'  => [
                    'group_number' => $group_number,
                    'plugin_ticketalerts_alertgroups_id' => $alertgroups_id,
                    ['rank'  => ['>', $old_rank]],
                    ['rank'  => ['<=', $rank]]
                ]
            ]);
            foreach ($iterator as $data) {
                $data['rank']--;
                $result = $criteria->update($data);
            }
        } else if ($old_rank > $rank) {
            if ($type == self::MOVE_AFTER) {
                $rank++;
            }

            // Move forward all rule  between old and new rank
            $iterator = $DB->request([
                'SELECT' => ['id', 'rank'],
                'FROM'   => $criteria->getTable(),
                'WHERE'  => [
                    'group_number' => $group_number,
                    'plugin_ticketalerts_alertgroups_id' => $alertgroups_id,
                    ['rank'  => ['>=', $rank]],
                    ['rank'  => ['<', $old_rank]]
                ]
            ]);
            foreach ($iterator as $data) {
                $data['rank']++;
                $result = $criteria->update($data);
            }
        } else { // $old_rank == $rank : nothing to do
            $result = false;
        }

        // Move the rule
        if ($result && ($old_rank != $rank)) {
            $result = $criteria->update([
                'id'      => $ID,
                'rank' => $rank
            ]);
        }
        return ($result ? true : false);
    }
}
