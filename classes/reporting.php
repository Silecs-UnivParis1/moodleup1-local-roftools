<?php
namespace local_roftools;

require_once($CFG->dirroot . '/local/roftools/roflib.php'); // to get ROF data

class reporting {

    private $viewobject = '/local/roftools/viewobject.php';

    /**
     *
     * @global moodle_database $DB
     * @return array
     */
    public static function get_overview() {
        global $DB;
        $res = array();

        $count = $DB->count_records('rof_constant');
        $res[] = array('Constantes', $count);
        $sql = 'SELECT COUNT(DISTINCT element) FROM {rof_constant}';
        $res[] = array('Constantes éléments', $DB->get_field_sql($sql));
        $count = $DB->count_records('rof_component');
        $res[] = array('Composantes', $count);
        $res[] = array('', ''); //** @todo meilleur séparateur ?
        $count = $DB->count_records('rof_program', array('level' => 1));
        $res[] = array('Programmes', $count);
        $count = $DB->count_records('rof_program', array('level' => 2));
        $res[] = array('Sous-programmes', $count);
        $count = $DB->count_records('rof_course');
        $res[] = array('Cours ROF', $count);
        $res[] = array('', ''); //** @todo meilleur séparateur ?
        $count = $DB->count_records('rof_person');
        $res[] = array('Personnes', $count);

        return $res;
    }

    /**
     *
     * @global moodle_database $DB
     * @return array
     */
    public static function get_courses() {
        global $DB;
        $res = array();

        $count = $DB->count_records('rof_course');
        $res[] = array('Cours ROF', $count);
        $count = $DB->count_records('rof_course', array('subnb' => 0));
        $res[] = array('Cours feuilles', $count);
        $res[] = array('', ''); //** @todo meilleur séparateur ?
        $levelmax = $DB->get_record_sql('SELECT MAX(level) as levelmax FROM {rof_course} rc')->levelmax;
        for ($level = 1; $level <= 1+$levelmax ; $level++) {
           $count = $DB->count_records('rof_course', array('level' => $level));
           $res[] = array('Cours de niveau=' . $level, $count);
        }
        return $res;
    }

    /**
     *
     * @global moodle_database $DB
     * @return array
     */
    public static function get_components() {
        global $DB;
        $res = array();

        $progsmax = $DB->get_record_sql('SELECT MAX(subnb) as progsmax FROM {rof_component}')->progsmax;
        $progsmin = $DB->get_record_sql('SELECT MIN(subnb) as progsmin FROM {rof_component} WHERE subnb>0')->progsmin;

        $components = $DB->get_records('rof_component', array('subnb' => $progsmax));
        foreach ($components as $component) {
            $res[] = array('Max', $progsmax, $component->number, $component->name);
        }
        $components = $DB->get_records('rof_component', array('subnb' => $progsmin));
        foreach ($components as $component) {
            $res[] = array('Min > 0', $progsmin, $component->number, $component->name);
        }
        $components = $DB->get_records('rof_component', array('subnb' => 0));
        foreach ($components as $component) {
            $res[] = array('None', '0', $component->number, $component->name);
        }
        return $res;
    }

    /**
     *
     * @global moodle_database $DB
     * @return array
     */
    public static function get_persons_not_empty() {
        global $DB;
        $res = array();

        $count = $DB->count_records_sql("SELECT COUNT(id) FROM {rof_program} WHERE level=1 AND refperson != ''");
        $res[] = array('Programs', $count);
        $count = $DB->count_records_sql("SELECT COUNT(id) FROM {rof_program} WHERE level=2 AND refperson != ''");
        $res[] = array('SubPrograms', $count);
        $res[] = array('', ''); //** @todo meilleur séparateur ?

        $count = $DB->count_records_sql("SELECT COUNT(id) FROM {rof_course} WHERE refperson != ''");
           $res[] = array('Courses', $count);
        $levelmax = $DB->get_record_sql('SELECT MAX(level) as levelmax FROM {rof_course} rc')->levelmax;
        for ($level = 1; $level <= $levelmax ; $level++) {
           $count = $DB->count_records_sql(
                   "SELECT COUNT(id) FROM {rof_course} WHERE level=? AND refperson != ''", array($level));
           $res[] = array('Cours de niveau=' . $level, $count);
        }
        return $res;
    }

    /**
     *
     * @global \moodle_database $DB
     * @return array(array(string)) : table rows
     */
    public static function get_hybrid_programs() {
        global $DB;
        $res = array();
        $programs = $DB->get_records_sql("SELECT rofid, name, subnb, coursesnb "
            . "FROM {rof_program} "
            . "WHERE level=1 AND subnb>0 AND coursesnb>0");

        foreach ($programs as $program) {
            $url = new moodle_url(self::$viewobject, array('rofid'=>$program->rofid));
            $res[] = array (
                html_writer::link($url, $program->rofid),
                $program->name,
                $program->subnb,
                $program->coursesnb
            );
        }
        return $res;
    }


    /**
     * list the ROF cached objects (components, programs, courses) with a local name
     * (different from official name)
     * @global \moodle_database $DB
     * @return array(array(string)) : table rows
     */
    public static function get_localnames() {
        global $DB;
        $res = array();
        $objects = array('component', 'program', 'course');

        foreach ($objects as $object) {
            $sql = sprintf("SELECT rofid, name, localname "
                . "FROM {rof_%s} WHERE localname != '' ", $object);
            $items = $DB->get_records_sql($sql);

            foreach ($items as $item) {
                $url = new moodle_url(self::$viewobject, array('rofid' => $item->rofid));
                $res[] = array (
                    $object,
                    html_writer::link($url, $item->rofid),
                    $item->name,
                    $item->localname
                );
            }
        }
        return $res;
    }
}