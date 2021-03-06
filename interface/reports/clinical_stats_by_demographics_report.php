<?php
/**
 * interface/reports/clinical_stats_by_demographic.php: Lists prodcedures by demographics,
 *integrates dataTables in report.  Ability to download in .pdf, .xls, or .csv.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/mpl-license.php>;.
 * Copyright (c) 2018 Growlingflea Software <daniel@growlingflea.com>
 * File adapted for user activity log.
 * @package LibreEHR
 * @author  Daniel Pflieger daniel@growlingflea.com daniel@mi-squared.com
 */
 $fake_register_globals=false;
 $sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/vendor/libreehr/Framework/DataTable/DataTable.php");
require_once "reports_controllers/ClinicalController.php";
$DateFormat = DateFormatRead();
//make sure to get the dates
if ( ! isset($_POST['form_from_date'])) {

    $from_date = fixDate(date($DateFormat));

} else {
    $from_date = fixDate($_POST['form_from_date']);
}

if ( !isset($_POST['form_to_date'])) {
    // If a specific patient, default to 2 years ago.
    $to_date = fixDate(date($DateFormat));


} else{

    $to_date = fixDate($_POST['form_to_date']);
}

$to_date = new DateTime($to_date);
$to_date->modify('+1 day');
$to_date = $to_date->format('Y-m-d');



?>
<head>
<?php html_header_show();?>
<title><?php xl('Clinical Reports: Demographics vs Diagnosis','e'); ?></title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">
@import "<?php echo $GLOBALS['webroot'] ?>/assets/js/datatables/media/css/demo_page.css";
@import "<?php echo $GLOBALS['webroot'] ?>/assets/js/datatables/media/css/demo_table.css";
@import "<?php echo $GLOBALS['webroot'] ?>/assets/js/css/jquery-ui-1-12-1/jquery-ui.css";

<!-- @import "<?php echo $GLOBALS['webroot'] ?>/library/css/jquery.tooltip.css"; -->
.mytopdiv { float: left; margin-right: 1em; }
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/assets/js/datatables/media/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/assets/js/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/tooltip.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/assets/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
<script type='text/javascript' src='<?php echo $GLOBALS['webroot'] ?>/library/dialog.js'></script>
<link href="../../../../dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/assets/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/assets/js/DataTables-1.10.16/datatables.css">
<script type="text/javascript" charset="utf8" src="<?php echo $GLOBALS['webroot'] ?>/assets/js/DataTables-1.10.16/datatables.js"></script>
<!-- this is a 3rd party script -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/assets/js/datatables/extras/ColReorder/media/js/ColReorderWithResize.js"></script>
<link rel="stylesheet" href="../../library/css/jquery.datetimepicker.css">
<script>
$(document).ready(function() {

    if($('#show_diags_details_selector').val()) {
        console.log('asdasdasdasd');
        $('.session_table').hide();
        $('#show_diags_details_table').show();

        show_all_diags();
        console.log('end of function');
    }












});


var oTable;
// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
    var f = document.forms[0][current_sel_name];
    var s = f.value;
    if (code) {
        if (s.length > 0) s += ';';
        s += codetype + ':' + code;
    } else {
        s = '';
    }
    f.value = s;
}

//This invokes the find-code popup.
function sel_diagnosis(e) {
    current_sel_name = e.name;
    dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo collect_codetypes("diagnosis","csv"); ?>', '_blank', 500, 400);
}

//This invokes the find-code popup.
function sel_procedure(e) {
    current_sel_name = e.name;
    dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo collect_codetypes("procedure","csv"); ?>', '_blank', 500, 400);
}
$("#form_from_date").val();
//Function to initiate datatables plugin


function refreshPage(){

    window.location.reload();

}

function show_all_diags(){

    $('#image').show();

    oTable=$('#show_diags_details_table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'excel', 'pdf', 'csv'
        ],
        ajax:{
            type: "POST",
            url: "../../library/ajax/clinical_stats_and_lab_stats_by_demographics_report_ajax.php",
            data: {
                func:"get_all_diags_data",
                diag:"<?php  echo $_POST['form_diagnosis']   ?>",
                proc:"<?php  echo $_POST['form_service_codes']   ?>"
            }, complete: function(){
                $('#image').hide();
            }},
        columns:[
            { 'data': 'pid'         },
            { 'data': 'sex'      },
            { 'data': 'dob'         },
            { 'data': 'ethnicity'   },
            { 'data': 'diagnosis' },
            { 'data': 'title'      }


        ],
        "iDisplayLength": 100,
        "select":true,
        "searching":true,
        "retrieve" : true
    });

    $('#column0_search_show_diags_details_table').on( 'keyup', function () {
        oTable
            .columns( 0 )
            .search( this.value )
            .draw();
    } );

    $('#column1_search_show_diags_details_table').on( 'keyup', function () {
        oTable
            .columns( 1 )
            .search( this.value)
            .draw();
    } );

    $('#column2_search_show_diags_details_table').on( 'keyup', function () {
        oTable
            .columns( 2 )
            .search( this.value )
            .draw();
    } );

    $('#column3_search_show_diags_details_table').on( 'keyup', function () {
        oTable
            .columns( 3 )
            .search( this.value )
            .draw();
    } );

    $('#column4_search_show_diags_details_table').on( 'keyup', function () {
        oTable
            .columns( 4 )
            .search( this.value )
            .draw();
    } );

    $('#column5_search_show_diags_details_table').on( 'keyup', function () {
        oTable
            .columns( 5 )
            .search( this.value )
            .draw();
    } );



}


</script>
</head>
<body class="body_top formtable">&nbsp;&nbsp;
<form action="./clinical_stats_by_demographics_report.php" method="post">
<table>
<tr>
<td><label><input value="Show All Diagnoses" type="submit" id="show_diags_details_selector" name="show_diags_details" ><?php ?></label></td>

</tr>


    <tr>

    <td class='label'><?php echo htmlspecialchars(xl('Problem DX'),ENT_NOQUOTES); ?>:</td>
    <td><input type='text' name='form_diagnosis' size='10' maxlength='250'
               value='<?php echo htmlspecialchars($form_diagnosis, ENT_QUOTES); ?>'
               onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'), ENT_QUOTES); ?>' readonly/>
    </td>
    <td>&nbsp;</td>

    </tr>


    <tr>
        <td class='label' width='76'>
            <?php echo htmlspecialchars(xl('Procedure Code'), ENT_NOQUOTES); ?>:</td>
        <td><input type='text' name='form_service_codes' size='10' maxlength='250'
                   value='<?php echo htmlspecialchars($form_service_codes, ENT_QUOTES); ?>'
                   onclick='sel_procedure(this)' title='<?php echo htmlspecialchars(xl('Click to select or change service codes'), ENT_QUOTES); ?>' readonly/>&nbsp;
        </td>

    </tr>

    <tr><td>

            <input hidden id = 'show_diags_details_button' value = '<?php echo isset($_POST['show_diags_details']) ? $_POST['show_diags_details'] : null  ?>'>
    </td></tr>
</table>
</form>



&nbsp;&nbsp;

<img hidden id="image" src="/images/loading.gif" width="100" height="100">



<table cellpadding="0" cellspacing="0" border="0" class="display formtable session_table" id="show_diags_details_table">
	<thead>

        <tr>
            <th><input  id = 'column0_search_show_diags_details_table' size="4"></th>
            <th><input  id = 'column1_search_show_diags_details_table' size="4"></th>
            <th><input  id = 'column2_search_show_diags_details_table' size="4"></th>
            <th><input  id = 'column3_search_show_diags_details_table' size="4"></th>
            <th><input  id = 'column4_search_show_diags_details_table' size="4"></th>
            <th align="left"><input  id = 'column5_search_show_diags_details_table' size="4"></th>
        </tr>

		<tr>
			<th><?php echo xla('PID'); ?></th>
			<th><?php echo xla('Gender'); ?></th>
			<th><?php echo xla('DOB'); ?></th>
			<th><?php echo xla('ETHNICITY'); ?></th>
			<th><?php echo xla('ICD10'); ?></th>
            <th align="left"><?php echo xla('ICD10 Text'); ?></th>
		</tr>

	</thead>
	<tbody id="users_list">
	</tbody>
</table>
</body>
<link rel="stylesheet" href="../../library/css/jquery.datetimepicker.css">
<script type="text/javascript" src="../../library/js/jquery.datetimepicker.full.min.js"></script>

<script>
    $(function() {
        $("#form_from_date").datetimepicker({
            timepicker: false,
            format: "Y-m-d"
        });
        $("#form_to_date").datetimepicker({
            timepicker: false,
            format: "Y-m-d"
        });

    });
</script>
