<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'plottracker.php');

require_once "./global.php";
$lang->load('plottracker');

add_breadcrumb($lang->plottracker, "plottracker.php");

// Breadcrump Navigation
switch($mybb->input['action'])
{
    case "view":
        add_breadcrumb($lang->plottracker_view);
        break;
}

// Generate navigation
$query = $db->simple_select("plots", "*", "", [ "order_by" => 'startdate', "order_dir" => 'ASC' ]);
while($plot = $db->fetch_array($query)) {
    eval("\$menu_bit .= \"".$templates->get("plottracker_nav_bit")."\";"); 
}

eval("\$menu = \"".$templates->get("plottracker_nav")."\";");

// Landing Page
if(!$mybb->input['action'])
{
    eval("\$page = \"".$templates->get("plottracker")."\";");
    output_page($page);
}

// view plot
if($mybb->input['action'] == "view") {

    $plid = $mybb->get_input('plid');
    $query = $db->simple_select("plots", "*", "plid='{$plid}'");
    $plot = $db->fetch_array($query);

    // Format Entries
    require_once MYBB_ROOT."inc/class_parser.php";
    $parser = new postParser;
    $parser_options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1
    );

    $plot['text'] = $parser->parse_message($plot['text'], $parser_options);
    $plot['startdate'] = date("d.m.Y", $plot['startdate']);
    $plot['enddate'] = date("d.m.Y", $plot['enddate']);

    $query = $db->simple_select("plots_threads", "tid", "plid='{$plid}'");
    while($threadlist = $db->fetch_array($query)) {
        $thread = get_thread($threadlist['tid']);
        if($thread) {
            $query_2 = $db->simple_select("ipt_scenes_partners", "uid", "tid='{$thread['tid']}'");
            while($userlist = $db->fetch_array($query_2)) {
                $user = get_user($userlist['uid']);
                $username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
                $formattedname = build_profile_link($username, $userlist['uid']);
                $usernames .= "&nbsp; &nbsp; {$formattedname}";
            }
            eval("\$thread_list .= \"".$templates->get("plottracker_view_threads_bit")."\";");
        }
    }
    eval("\$threads = \"".$templates->get("plottracker_view_threads")."\";");


    eval("\$page = \"".$templates->get("plottracker_view")."\";");
    output_page($page);   
}

?>
