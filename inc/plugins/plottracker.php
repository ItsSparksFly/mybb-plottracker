<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("newthread_start", "plottracker_newthread");
$plugins->add_hook("newthread_do_newthread_end", "plottracker_do_newthread");
$plugins->add_hook("editpost_end", "plottracker_editpost");
$plugins->add_hook("editpost_do_editpost_end", "plottracker_do_editpost");

function plottracker_info()
{
    global $lang;
    $lang->load('plottracker');

    // Plugin Info
    $plottracker = [
        "name" => $lang->plottracker_name,
        "description" => $lang->plottracker_short_desc,
        "website" => "https://github.com/itssparksfly",
        "author" => "sparks fly",
        "authorsite" => "https://github.com/itssparksfly",
        "version" => "1.0",
        "compatibility" => "18*"
    ];

    return $plottracker;
}


function plottracker_install() {
    global $db;

    // Catch potential errors [duplicates]
    $tables = [
        "plots", "plots_threads"
    ];

    foreach($tables as $table) {
        if ($db->table_exists($table)) {
            $db->drop_table($table);
        }
    }

    $collation = $db->build_create_table_collation();

    $db->write_query("
        CREATE TABLE ".TABLE_PREFIX."plots (
            `plid` int(10) unsigned NOT NULL auto_increment,
            `name` varchar(255) NOT NULL DEFAULT '',
            `text` text NOT NULL,
            `startdate` varchar(255) NOT NULL,
            `enddate` varchar(255) NOT NULL, 
            PRIMARY KEY (plid)
        ) ENGINE=MyISAM{$collation};
    ");

    $db->write_query("
        CREATE TABLE ".TABLE_PREFIX."plots_threads (
            `pltid` int(10) unsigned NOT NULL auto_increment,
            `tid` int(10) NOT NULL,
            `plid` int(10) NOT NULL, 
            PRIMARY KEY (pltid)
        ) ENGINE=MyISAM{$collation};
    ");
}

function plottracker_is_installed() {
    global $db;

    if ($db->table_exists("plots") && $db->table_exists("plots_threads")) {
        return true;
    } else {
        return false;
    }
}

function plottracker_uninstall() {
    global $db;

    $tables = [
        "plots", "plots_threads"
    ];
    
    foreach($tables as $table) {
        if($db->table_exists($table)) {
            $db->drop_table($table);
        }
    }
}

function plottracker_activate() { 

    global $db;

    include MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("newthread", "#".preg_quote('{$prefixselect}')."#i", '{$prefixselect}{$newthread_plottracker}');
    find_replace_templatesets("editpost", "#".preg_quote('{$prefixselect}')."#i", '{$prefixselect}{$editpost_plottracker}');

    $plottracker = [
        'title' => 'plottracker',
        'template' => $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->plottracker}</title>
		{$headerinclude}</head>
		<body>
		{$header}
			<table width="100%" cellspacing="5" cellpadding="5">
				<tr>
					{$menu}
					<td valign="top" class="trow1">
						<div style="text-align: justify; width: 70%; margin: 20px auto;">
							{$lang->plottracker_desc} 
						</div>
					</td>
				</tr>
			</table>
		{$footer}
		</body>
		</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker);

    $plottracker_nav = [
        'title' => 'plottracker_nav',
        'template' => $db->escape_string('<td width="20%" valign="top">
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
        <tbody>
            <tr>
                <td class="thead"><strong><a href="plottracker.php">{$lang->plottracker}</a></strong></td>
            </tr>
            {$menu_bit}
        </tbody>
        </table>
    </td>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker_nav);

    $plottracker_nav_bit = [
        'title' => 'plottracker_nav_bit',
        'template' => $db->escape_string('<tr>
		<td class="trow1 smalltext">&bull; &nbsp; <a href="plottracker.php?action=view&plid={$plot[\'plid\']}">{$plot[\'name\']}</td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker_nav_bit);

    $plottracker_newthread = [
        'title' => 'plottracker_newthread',
        'template' => $db->escape_string('<select name="plid" class="select">
        <option>{$lang->plottracker_select_plot}</option>
        {$plot_bit}
        </option>
    </select>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker_newthread);

    $plottracker_view = [
        'title' => 'plottracker_view',
        'template' => $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->plottracker}</title>
		{$headerinclude}</head>
		<body>
		{$header}
			<table width="100%" cellspacing="5" cellpadding="5">
				<tr>
					{$menu}
					<td valign="top" class="trow1" valign=\"top\">
						<br />
						<div class="thead">{$plot[\'name\']}</div>
						<div class="tcat">Von {$plot[\'startdate\']} bis {$plot[\'enddate\']}</div>
						<div style="text-align: justify; width: 70%; margin: 20px auto;">
							{$plot[\'text\']}<br /><br />
							{$threads}
						</div>
					</td>
				</tr>
			</table>
		{$footer}
		</body>
		</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker_view);

    $plottracker_view_threads = [
        'title' => 'plottracker_view_threads',
        'template' => $db->escape_string('<table cellspacing="2" cellpadding="5" width="100%">
        <tr>
            <td colspan="2" class="thead">
                {$lang->plottracker_threads}
            </td>
        </tr>
        {$thread_list}
    </table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker_view_threads);

    $plottracker_view_threads_bit = [
        'title' => 'plottracker_view_threads_bit',
        'template' => $db->escape_string('<tr>
        <td colspan="2" class="tcat"><a href="showthread.php?tid={$threadlist[\'tid\']}">{$thread[\'subject\']}</a></td>
    </tr>
    <tr>
        <td width="10%" class="trow1" align="center"><div style="font-size: 8px;"><strong>{$lang->plottracker_cast}</strong></div></td>
        <td class="trow1"><div style="font-size: 9px;">{$usernames}</div></td>
    </tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $plottracker_view_threads_bit);

}

function plottracker_deactivate() {
    global $db;

    include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("newthread", "#".preg_quote('{$newthread_plottracker}')."#i", '', 0);
    find_replace_templatesets("editpost", "#".preg_quote('{$editpost_plottracker}')."#i", '', 0);
    
	$db->delete_query("templates", "title LIKE '%plottracker%'");
}

// ACP Action Handler
$plugins->add_hook("admin_config_action_handler", "plottracker_admin_config_action_handler");
function plottracker_admin_config_action_handler(&$actions)
{
    $actions['plottracker'] = array('active' => 'plottracker', 'file' => 'plottracker');
}

// ACP Permissions
$plugins->add_hook("admin_config_permissions", "plottracker_admin_config_permissions");
function plottracker_admin_config_permissions(&$admin_permissions)
{
    global $lang;
    $lang->load('plottracker');

    $admin_permissions['plottracker'] = $lang->plottracker_permission;
}

// ACP menu 
$plugins->add_hook("admin_config_menu", "plottracker_admin_config_menu");
function plottracker_admin_config_menu(&$sub_menu)
{
    global $mybb, $lang;
    $lang->load('plottracker');
    
    $sub_menu[] = [
        "id" => "plottracker",
        "title" => $lang->plottracker_manage,
        "link" => "index.php?module=config-plottracker"
    ];
}

// Manage Plots in ACP
$plugins->add_hook("admin_load", "plottracker_manage_plottracker");
function plottracker_manage_plottracker() 
{
    global $mybb, $db, $lang, $page, $run_module, $action_file;
    $lang->load('plottracker');

    if ($page->active_action != 'plottracker') {
        return false;
    }

    if ($run_module == 'config' && $action_file == 'plottracker') {
        // plottracker Overview
        if ($mybb->input['action'] == "" || !isset($mybb->input['action'])) {

            // Add to page navigation
            $page->add_breadcrumb_item($lang->plottracker_manage);

            // Build options header
            $page->output_header($lang->plottracker_manage." - ".$lang->plottracker_manage_overview_entries);
            $sub_tabs['plottracker'] = [
                "title" => $lang->plottracker_manage_overview_entries,
                "link" => "index.php?module=config-plottracker",
                "description" => $lang->plottracker_manage_overview_entries_desc
                
            ];
            $sub_tabs['plottracker_entry_add'] = [
                "title" => $lang->plottracker_manage_add_entry,
                "link" => "index.php?module=config-plottracker&amp;action=add_entry",
                "description" => $lang->plottracker_manage_add_entry_desc
            ];

            $page->output_nav_tabs($sub_tabs, 'plottracker');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Build the overview
            $form = new Form("index.php?module=config-plottracker", "post");

            $form_container = new FormContainer($lang->plottracker_manage_edit_entry);
            $form_container->output_row_header($lang->plottracker_manage_title);
            $form_container->output_row_header("<div style=\"text-align: center;\">".$lang->plottracker_manage_options."</div>");

            // Get all entries
            $query = $db->simple_select("plots", "*", "",
                ["order_by" => 'name', 'order_dir' => 'ASC']);
 
            while($plottracker_entries = $db->fetch_array($query)) {

                $form_container->output_cell('<strong>'.htmlspecialchars_uni($plottracker_entries['name']).'</strong>');
                $popup = new PopupMenu("plottracker_{$plottracker_entries['plid']}", $lang->plottracker_manage_edit);
                $popup->add_item(
                    $lang->plottracker_manage_edit,
                    "index.php?module=config-plottracker&amp;action=edit_entry&amp;plid={$plottracker_entries['plid']}"
                );
                $popup->add_item(
                    $lang->plottracker_manage_delete,
                    "index.php?module=config-plottracker&amp;action=delete_entry&amp;plid={$plottracker_entries['plid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
                $form_container->construct_row();
            }

            $form_container->end();
            $form->end();
            $page->output_footer();

            exit;
        }
        if ($mybb->input['action'] == "add_entry") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['name'])) {
                    $errors[] = $lang->plottracker_manage_error_no_title;
                }
                if (empty($mybb->input['text'])) {
                    $errors[] = $lang->plottracker_manage_error_no_text;
                }

                // No errors - insert
                if (empty($errors)) {

                    $startdate = strtotime($db->escape_string($mybb->input['startdate']));
                    $enddate = strtotime($db->escape_string($mybb->input['enddate']));

                    $new_entry = array(
                        "name" => $db->escape_string($mybb->input['name']),
                        "text" => $db->escape_string($mybb->input['text']),
                        "startdate" => $startdate,
                        "enddate" => $enddate
                    );

                    $db->insert_query("plots", $new_entry);

                    $mybb->input['module'] = "plottracker";
                    $mybb->input['action'] = $lang->plottracker_manage_entry_added;
                    log_admin_action(htmlspecialchars_uni($mybb->input['name']));

                    flash_message($lang->plottracker_manage_entry_added, 'success');
                    admin_redirect("index.php?module=config-plottracker");
                }
            }

                $page->add_breadcrumb_item($lang->plottracker_manage_add_entry);
                // Editor scripts
                $page->extra_header .= <<<EOF
	<link rel="stylesheet" href="../jscripts/sceditor/themes/mybb.css" type="text/css" media="all" />
	<script type="text/javascript" src="../jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver=1822"></script>
	<script type="text/javascript" src="../jscripts/bbcodes_sceditor.js?ver=1827"></script>
	<script type="text/javascript" src="../jscripts/sceditor/plugins/undo.js?ver=1805"></script>
EOF;

                // Build options header
                $page->output_header($lang->plottracker_manage." - ".$lang->plottracker_manage_overview_entries);
                $sub_tabs['plottracker'] = [
                    "title" => $lang->plottracker_manage_overview_entries,
                    "link" => "index.php?module=config-plottracker",
                    "description" => $lang->plottracker_manage_overview_entries_desc
                    
                ];
                $sub_tabs['plottracker_entry_add'] = [
                    "title" => $lang->plottracker_manage_add_entry,
                    "link" => "index.php?module=config-plottracker&amp;action=add_entry",
                    "description" => $lang->plottracker_manage_add_entry_desc
                ];

                $page->output_nav_tabs($sub_tabs, 'plottracker_entry_add'); 

                // Show errors
                if (isset($errors)) {
                    $page->output_inline_error($errors);
                }

                // Build the form
                $form = new Form("index.php?module=config-plottracker&amp;action=add_entry", "post", "", 1);
                $form_container = new FormContainer($lang->plottracker_manage_add_entry);

                $form_container->output_row(
                    $lang->plottracker_manage_title."<em>*</em>",
                    $lang->plottracker_manage_entry_name_desc,
                    $form->generate_text_box('name', $mybb->input['name'])
                );

                $form_container->output_row(
                    $lang->plottracker_manage_startdate."<em>*</em>",
                    $lang->plottracker_manage_startdate_desc,
                    $form->generate_text_box('startdate', $mybb->input['startdate'])
                );

                $form_container->output_row(
                    $lang->plottracker_manage_enddate."<em>*</em>",
                    $lang->plottracker_manage_enddate_desc,
                    $form->generate_text_box('enddate', $mybb->input['enddate'])
                );

                $text_editor = $form->generate_text_area('text', $mybb->input['text'], array(
                    'id' => 'text',
                    'rows' => '25',
                    'cols' => '70',
                    'style' => 'height: 450px; width: 75%'
                    )
                );

                $text_editor .= build_mycode_inserter('text');
                $form_container->output_row(
                    $lang->plottracker_manage_content. "<em>*</em>",
                    $lang->plottracker_manage_entry_title_desc,
                    $text_editor,
                    'text'
                );

                $form_container->end();
                $buttons[] = $form->generate_submit_button($lang->plottracker_manage_submit);
                $form->output_submit_wrapper($buttons);
                $form->end();
                $page->output_footer();
    
                exit;         
        }
        if ($mybb->input['action'] == "edit_entry") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['name'])) {
                    $errors[] = $lang->plottracker_manage_error_no_title;
                }
                if (empty($mybb->input['text'])) {
                    $errors[] = $lang->plottracker_manage_error_no_text;
                }

                // No errors - insert the terms of use
                if (empty($errors)) {
                    $plid = $mybb->get_input('plid', MyBB::INPUT_INT);
                    $startdate = strtotime($db->escape_string($mybb->input['startdate']));
                    $enddate = strtotime($db->escape_string($mybb->input['enddate']));

                    $edited_entry = [
                        "name" => $db->escape_string($mybb->input['name']),
                        "text" => $db->escape_string($mybb->input['text']),
                        "startdate" => $startdate,
                        "enddate" => $enddate
                    ];

                    $db->update_query("plots", $edited_entry, "plid='{$plid}'");

                    $mybb->input['module'] = "plottracker";
                    $mybb->input['action'] = $lang->plottracker_manage_entry_edited;
                    log_admin_action(htmlspecialchars_uni($mybb->input['name']));

                    flash_message($lang->plottracker_manage_entry_edited, 'success');
                    admin_redirect("index.php?module=config-plottracker");
                }

            }
            
            $page->add_breadcrumb_item($lang->plottracker_manage_edit_entry);

            // Editor scripts
            $page->extra_header .= <<<EOF
<link rel="stylesheet" href="../jscripts/sceditor/themes/mybb.css" type="text/css" media="all" />
<script type="text/javascript" src="../jscripts/sceditor/jquery.sceditor.bbcode.min.js?ver=1822"></script>
<script type="text/javascript" src="../jscripts/bbcodes_sceditor.js?ver=1827"></script>
<script type="text/javascript" src="../jscripts/sceditor/plugins/undo.js?ver=1805"></script>
EOF;

            // Build options header
            $page->output_header($lang->plottracker_manage." - ".$lang->plottracker_manage_overview_entries);
            $sub_tabs['plottracker'] = [
                "title" => $lang->plottracker_manage_overview_entries,
                 "link" => "index.php?module=config-plottracker",
                "description" => $lang->plottracker_manage_overview_entries_desc
                 
            ];

            $page->output_nav_tabs($sub_tabs, 'plottracker'); 

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Get the data
            $plid = $mybb->get_input('plid', MyBB::INPUT_INT);
            $query = $db->simple_select("plots", "*", "plid={$plid}");
            $edit_entry = $db->fetch_array($query);

            // Build the form
            $form = new Form("index.php?module=config-plottracker&amp;action=edit_entry", "post", "", 1);
            echo $form->generate_hidden_field('plid', $plid);

            $form_container = new FormContainer($lang->plottracker_manage_edit_entry);
            $form_container->output_row(
                $lang->plottracker_manage_title,
                $lang->plottracker_manage_entry_name_desc,
                $form->generate_text_box('name', htmlspecialchars_uni($edit_entry['name']))
            );

            $edit_entry['startdate'] = date("d.m.Y", $edit_entry['startdate']);
            $edit_entry['enddate'] = date("d.m.Y", $edit_entry['enddate']);

            $form_container->output_row(
                $lang->plottracker_manage_startdate."<em>*</em>",
                $lang->plottracker_manage_startdate_desc,
                $form->generate_text_box('startdate', $edit_entry['startdate'])
            );

            $form_container->output_row(
                $lang->plottracker_manage_enddate."<em>*</em>",
                $lang->plottracker_manage_enddatedesc,
                $form->generate_text_box('enddate', $edit_entry['enddate'])
            );

            $text_editor = $form->generate_text_area('text', $edit_entry['text'], array(
                    'id' => 'text',
                    'rows' => '25',
                    'cols' => '70',
                    'style' => 'height: 450px; width: 75%'
                )
            );
            $text_editor .= build_mycode_inserter('text');
            $form_container->output_row(
                $lang->plottracker_manage_content,
                $lang->plottracker_manage_entry_title_desc,
                $text_editor,
                'text'
            );
 
            $form_container->end();
            $buttons[] = $form->generate_submit_button($lang->plottracker_manage_submit);
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();

            exit;
        }
       // Delete entry
       if ($mybb->input['action'] == "delete_entry") {
            // Get data
            $plid = $mybb->get_input('plid', MyBB::INPUT_INT);
            $query = $db->simple_select("plots", "*", "plid={$plid}");
            $del_entry = $db->fetch_array($query);

            // Error Handling
            if (empty($plid)) {
                flash_message($lang->plottracker_manage_error_invalid, 'error');
                admin_redirect("index.php?module=config-plottracker");
            }

            // Cancel button pressed?
            if (isset($mybb->input['no']) && $mybb->input['no']) {
                admin_redirect("index.php?module=config-plottracker");
            }

            if (!verify_post_check($mybb->input['my_post_key'])) {
                flash_message($lang->invalid_post_verify_key2, 'error');
                admin_redirect("index.php?module=config-plottracker");
            }  // all fine
            else {
                if ($mybb->request_method == "post") {
                    
                    $db->delete_query("plots", "plid='{$plid}'");

                    $mybb->input['module'] = "plottracker";
                    $mybb->input['action'] = $lang->plottracker_manage_entry_deleted;
                    log_admin_action(htmlspecialchars_uni($del_entry['name']));

                    flash_message($lang->plottracker_manage_entry_deleted, 'success');
                    admin_redirect("index.php?module=config-plottracker");
                } else {
                    $page->output_confirm_action(
                        "index.php?module=config-plottracker&amp;action=delete_entry&amp;plid={$plid}",
                        $lang->plottracker_manage_delete
                    );
                }
            }
            exit;
        }
    }
}

function plottracker_newthread() {
    global $mybb, $lang, $db, $templates, $post_errors, $forum, $newthread_plottracker;
    $lang->load('plottracker');

    $newthread_plottracker = "";

    // insert plottracker options 
    $selectedforums = explode(",", $mybb->settings['ipt_inplay']);
    $forum['parentlist'] = ",".$forum['parentlist'].",";
    foreach($selectedforums as $selected) {
		if(preg_match("/,$selected,/i", $forum['parentlist'])) {
		// previewing new thread?
		if(isset($mybb->input['previewpost']) || $post_errors) {
		    $plot = $mybb->get_input('plid');
		}
		$query = $db->simple_select("plots", "plid, name");
		$plot_bit = "";
		while($plots = $db->fetch_array($query)) {
		    $selected = "";
		    if($plot == $plots['plid']) {
			$selected = "selected";
		    }
		    $plot_bit .= "<option value=\"{$plots['plid']}\" {$selected}>{$plots['name']}</option>";
		}
		eval("\$newthread_plottracker = \"".$templates->get("plottracker_newthread")."\";");
	}
    }
}

function plottracker_do_newthread() {
    global $db, $mybb, $tid;
    
    if(!empty($mybb->get_input('plid'))) {
        // insert thread infos into database   
        $new_record = [
            "plid" => (int)$mybb->get_input('plid'),
            "tid" => (int)$tid
        ];
        $db->insert_query("plots_threads", $new_record);
    }
}

function plottracker_editpost() {

    global $mybb, $db, $lang, $templates, $post_errors, $forum, $thread, $pid, $editpost_plottracker;
    $lang->load('plottracker');
    
    $editpost_plottracker = "";

    $inplaykategorie = $mybb->settings['inplaytracker_forum'];
    $forum['parentlist'] = ",".$forum['parentlist'].",";
	if(preg_match("/,$inplaykategorie,/i", $forum['parentlist'])) {
        $pid = $mybb->get_input('pid', MyBB::INPUT_INT);
        if($thread['firstpost'] == $pid) {
            $query = $db->simple_select("plots_threads", "plid", "tid='{$thread['tid']}'");
            $plots = $db->fetch_array($query);
            
            if(isset($mybb->input['previewpost']) || $post_errors) {
                $plot = htmlspecialchars_uni($mybb->get_input('plid'));
            }
            else
            {
                $plot = htmlspecialchars_uni($plots['plid']);
            }    

            $query = $db->simple_select("plots", "plid, name");              
            $plot_bit = "";
            while($plots = $db->fetch_array($query)) {
                $selected = "";
                if($plot == $plots['plid']) {
                    $selected = "selected";
                }
                $plot_bit .= "<option value=\"{$plots['plid']}\" {$selected}>{$plots['name']}</option>";
            }
            eval("\$editpost_plottracker = \"".$templates->get("plottracker_newthread")."\";");
        }
    }    
}

function plottracker_do_editpost()
{
    global $db, $mybb, $tid, $pid, $thread, $partners_new, $partner_uid;

    if($pid != $thread['firstpost']) {
		return;
	}

    if(!empty($mybb->get_input('plid'))) {
        $db->delete_query("plots_threads", "tid='{$tid}'");
        
        $new_record = [
            "plid" => (int)$mybb->get_input('plid'),
            "tid" => (int)$tid
        ];

        $db->insert_query("plots_threads", $new_record, "tid='{$tid}'");
    }
}
?>
