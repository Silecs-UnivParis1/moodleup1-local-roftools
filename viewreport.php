<?php
/**
 * ROF Statistics
 *
 * @package    local_roftools
 * @copyright  2012-2020 Silecs {@link http://www.silecs.info/societe}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \local_roftools\reporting;

require('../../config.php');
require_once($CFG->dirroot.'/local/roftools/rofcourselib.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
//admin_externalpage_setup('local_roftools_viewreport', '', null, '', ['pagelayout' => 'report']);

/* @var $PAGE moodle_page */
global $PAGE, $OUTPUT;
$PAGE->set_context(context_system::instance());
$PAGE->set_url("{$CFG->wwwroot}/local/roftools/viewreport.php");
$PAGE->navbar->add('Stats ROF');
//$PAGE->set_pagelayout('report');

// Print the header.
$titre = 'UP1 Statistiques ROF';
echo $OUTPUT->header($titre);
echo $OUTPUT->heading($titre);

$browserurl = "$CFG->wwwroot/local/rof_browser/rof_browser.php";
echo '<div><a href="' . $browserurl. '">ROF browser</a></div>';

echo "<h3>Compteurs</h3>\n";
$table = new html_table();
$table->head = array('Items', 'Nb');
$table->data = reporting::get_overview();
echo html_writer::table($table);

echo rof_links_constants('/local/roftools/viewconstant.php');

echo "<h3>Composantes</h3>\n";
$table = new html_table();
$table->head = array('', '# Programmes', 'Id. ROF', 'Nom');
$table->data = reporting::get_components();
echo html_writer::table($table);

echo "<h3>Cours ROF</h3>\n";
$table = new html_table();
$table->head = array('Items', 'Nb');
$table->data = reporting::get_courses();
echo html_writer::table($table);

echo "<h3>Personnes</h3>\n";
$table = new html_table();
$table->head = array('Niveaux', 'Personnes non vides');
$table->data = reporting::get_persons_not_empty();
echo html_writer::table($table);


echo "<h2>Anomalies ?</h2>";

echo "<h3>Programmes hybrides</h3>\n";
$table = new html_table();
$table->head = array('Programme', 'Titre', 'ss-prog.', 'cours');
$table->data = reporting::get_hybrid_programs();
echo html_writer::table($table);

echo "<h3>Noms locaux</h3>\n";
$table = new html_table();
$table->head = array('Objet', 'ROFid', 'Nom ROF', 'Nom local');
$table->data = reporting::get_localnames();
echo html_writer::table($table);

echo "<h3>Références cassées</h3>";
echo "<p>Liste les cours faisant référence à un objet ROF absent des tables de cache ROF.</p>";
rof_check_courses_references();

echo '<div><a href="' . $browserurl. '">ROF browser</a></div>';

echo $OUTPUT->footer();
