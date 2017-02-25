<?php 

header('Content-Type: application/json');

include 'src/Slack.php';
$config = parse_ini_file("config.ini");
$slack = new Slack($config['SLACK_TOKEN']);

$data = $slack->call('users.list', ['presence' => true]);


if (isset($data['members'])) {
    $users=[];
    foreach ($data['members'] as $member) {
        if (isset($member['presence']) && $member['presence'] == "active") {
            $users[] = $member;
        }
    }
    $index = array_rand($users);
    $user_id = $users[$index]['id'];
    $username = $users[$index]['name'];
} else {
    die(json_encode([
        'ok'    => false,
        'error' => 'Failed Connection to Slack',
        'response' => $data
    ]));
}


$request = $_POST;

if (!empty($request['token']) && $request['token'] == $config['COMMAND_TOKEN']) {
    $command = (!empty($request['command'])) ? $request['command'] : '';
    $text = (!empty($request['text'])) ? $request['text'] : '';
    $username = (!empty($request['user_name'])) ? $request['user_name'] : '';
    
    switch ($command) {
        case $config['COMMAND_NAME'][0]:
                $response_text = str_replace('{text}', $text, $config['TEXT']);
                $response_text = str_replace('{user}', "<@$user_id|$username>", $response_text);
                $response = [
                    'response_type' => 'in_channel',
                    'text' => $response_text,
                    'username' => "markdownbot",
                    'mrkdwn' => true

                ];
                $response = json_encode($response);
                echo $response;
        
            break;

    }
} else {
    die(json_encode([
        'ok'    =>false,
        'error' => 'Close Connection, is\'nt a post request'
    ]));
}
