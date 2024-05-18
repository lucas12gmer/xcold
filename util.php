<?php
require_once('settings.php');

function format_datetime($time) {
  $date = strtotime($time);
  return date('d.m.Y H:i', $date);
}

function print_interval($start_time, $end_time) {
  if ($start_time > $end_time) {
      return '-';
  }
  $diff = $end_time - $start_time;
  if ($diff > 315360000) { //more then 10 years
      return 'Permanent';
  }
  // Calculate time intervals
  $years = floor($diff / 31536000);
  $diff -= $years * 31536000;
  $months = floor($diff / 2628000);
  $diff -= $months * 2628000;
  $days = floor($diff / 86400);
  $diff -= $days * 86400;
  $hours = floor($diff / 3600);
  $diff -= $hours * 3600;
  $minutes = floor($diff / 60);
  $diff -= $minutes * 60;
  $seconds = $diff;

  // Format output string
  $output = '';
  if ($years > 0) {
    $output .= "$years years ";
  }
  if ($months > 0) {
    $output .= "$months months ";
  }
  if ($days > 0) {
    $output .= "$days days ";
  }
  if ($hours > 0) {
    $output .= "$hours hours ";
  }
  if ($minutes > 0) {
    $output .= "$minutes minutes ";
  }
  if ($seconds > 0) {
    $output .= "$seconds seconds";
  }

  return trim($output);
}

function get_name($uuid) {
  if ($onlineMode === false) {
      return $uuid;
  }
  $url = "https://sessionserver.mojang.com/session/minecraft/profile/$uuid";
  $context = stream_context_create(array(
    'http' => array(
      'method' => 'GET',
      'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)
                   AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
    )
  ));
  $json = file_get_contents($url, false, $context);

  $data = json_decode($json, true);
  return $data['name'];
}


function get_uuid($name) {
    if ($onlineMode === false) {
        return $name;
    }
    $json = file_get_contents('https://api.mojang.com/users/profiles/minecraft/' . $name);
    if (!empty($json)) {
        $data = json_decode($json, true);
        if (is_array($data) and !empty($data)) {
            if (is_array($data) and isset($data['id'])) {
                return $data['id'];
            }
        }
    }
    return false;
}

function get_uuid_with_hypens($name) {
  if ($onlineMode === false) {
      return $name;
  }
  $uuid = get_uuid($name);
  return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
}

function is_uuid_with_hyphens($uuid) {
  return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
}



function get_head64($name) {
    $defaultUUID = '28aa984a-2077-40cc-8de7-e641adf2c497'; // Default UUID for fallback
    $uuid = get_uuid($name); // Attempt to get UUID for the given name
    $url = 'https://crafatar.com/renders/head/' . ($uuid ? $uuid : $defaultUUID) . '?overlay';
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)
                         AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
        )
    ));
    $output = @file_get_contents($url, false, $context);

    // If fetching the head fails, try fetching the default head
    if ($output === FALSE || empty($output)) {
        $url = 'https://crafatar.com/renders/head/' . $defaultUUID . '?overlay';
        $output = file_get_contents($url, false, $context);
    }
    
    return base64_encode($output);
}

function hasPermission($conn, $uuid, $permission) {
    if ($uuid == 'admin') {
        return true;
    } else {
        $stmt = $conn->prepare("SELECT $permission FROM users WHERE uuid=?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();

        $result = $stmt->get_result();
        $info = $result->fetch_assoc();

        $hasPermission = $info[$permission] == '1';
        $stmt->close();

        return $hasPermission;        
    }
}

?>
