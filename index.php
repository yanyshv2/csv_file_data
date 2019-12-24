<?php
$mysql_conn = new mysqli("localhost", "root", "", "test-2");
if ($mysql_conn->connect_errno) {
    echo "MySQL Error: (" . $mysql_conn->connect_errno . ") " . $mysql_conn->connect_error;
}
// echo $mysql_conn->host_info; echo '<br><br>';

$mysql_conn->query(" 
CREATE TABLE IF NOT EXISTS users (
uid INT NOT NULL AUTO_INCREMENT, 
firstName VARCHAR(250) DEFAULT '' NOT NULL,
lastName VARCHAR(250) DEFAULT '' NOT NULL,
birthDay DATE NOT NULL,
dateChange DATE NOT NULL,
description TEXT NOT NULL,
PRIMARY KEY(uid)    
); 
    ");

$res_1 = $mysql_conn->query("SELECT * FROM users LIMIT 10");
$res_1_data = mysqli_fetch_all($res_1,MYSQLI_ASSOC);
if(!count($res_1_data)) {  // Add demo content in table
    $mysql_conn->query("
INSERT INTO users (firstName,lastName,birthDay,dateChange,description) VALUES 
('Edik','Brock','1990-12-01','2019-07-05','Edik description'), 
('Petro','Suker','1997-10-24','2019-09-15','Petro description'),
('Test 1','Suker','1997-10-21','2019-09-15',''),
('Test 2','Suker','1997-10-22','2019-09-15','');
");
}


$res_users = $mysql_conn->query("SELECT * FROM users");
$res_users_data = mysqli_fetch_all($res_users,MYSQLI_ASSOC);
$db_users_uids = array_column($res_users_data, 'uid');
$db_users_data = array_column($res_users_data, NULL, 'uid');



if($_FILES) { // upload .csv file

    $imp_posts_count = 0;
    $file_uri_5 = $_FILES['users_table']['tmp_name'];
    if ( is_file( $file_uri_5 ) ) {

        $rows_insert = 0;
        $rows_update = 0;
        $rows_delete = 0;
        $db_users_actual_uids = array();
        $csv_file_data = file($file_uri_5, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $db_field_definition = explode( "^", $csv_file_data[0] );
        unset($csv_file_data[0]);

        if(count($csv_file_data)) {
            foreach ($csv_file_data as $line_index => $line_content) {
                $data_definition = explode("^", $line_content);

                $data_def = array();
                foreach ($db_field_definition as $column_index => $column_name) {
                    $column_name = trim($column_name);
                    if (!empty($column_name)) {
                        $data_def[$column_name] = $data_definition[$column_index];
                    }
                }
                $user_uid = $data_def['uid'];
                $item_data = array_intersect_key($data_def, array('firstName' => '', 'lastName' => '', 'birthDay' => '', 'dateChange' => '', 'description' => ''));
                $item_data_str = "('" . implode("','", $item_data) . "')";
                $item_data_2 = array();
                foreach ($item_data as $key => $val) {
                    $item_data_2[] = "$key = '$val'";
                }
                $item_data_update_str = implode(', ', $item_data_2);

                if (in_array($user_uid, $db_users_uids)) { // /// edit row in DB (if 'dateChange' is bigger)
                    $db_users_actual_uids[] = $user_uid; // add user_uid to active list; other users will be deleted
                    if ($item_data['dateChange'] > $db_users_data[$user_uid]['dateChange']) {
                        $mysql_conn->query("UPDATE users SET $item_data_update_str WHERE uid = $user_uid;");
                        $rows_update++;
                    }
                } else { // /// add new row in DB
                    $mysql_conn->query("INSERT INTO users (firstName,lastName,birthDay,dateChange,description) VALUES $item_data_str;");
                    $rows_insert++;
                }

                $imp_posts_count++;
            } // __ foreach ( $csv_file_data as $line_index => $line_content )

            $db_users_uids_to_delete = array_diff($db_users_uids, $db_users_actual_uids);
            if(count($db_users_uids_to_delete)) {
                $rows_delete = count($db_users_uids_to_delete);
                foreach ($db_users_uids_to_delete as $uid_1) {
                    $mysql_conn->query("DELETE FROM users WHERE uid = $uid_1;");
                }
            }

            echo '<br><br> <h3>Data was updated successfully!</h3>';
        } // __ (count($csv_file_data))

        /*  */ // echo '<pre>'; print_r($errors_9); echo '</pre>';
        echo '<br><br>Insert: '.$rows_insert;
        echo '<br>Update: '.$rows_update;
        echo '<br>Delete: '.$rows_delete;

    }  // //////////////// if ( is_file( $file_uri_5 ) )

} // if($_FILES)








$res_2 = $mysql_conn->query("SELECT * FROM users ORDER BY birthDay DESC LIMIT 20");
$res_2_data = mysqli_fetch_all($res_2,MYSQLI_ASSOC);

echo '<pre>';
print_r($res_2_data); //
echo '</pre>';

// IF NOT EXISTS
// echo 5555999;
?>


<script type="text/javascript">
    function users_forma_check() {
        var file_el = document.getElementById("users_table_input");
        var file_name = file_el.value;
        var name_24 = file_name.split('.');   var ext = name_24[name_24.length-1];
        if ( (file_name.length < 3 ) )  {
            alert(" File name length must be at least 3! " );
            return false;
        }
        if ( ext !== 'csv' )  {
            alert(" Only .csv files! " );
            return false;
        }
    }
</script>
<form name="upload_users" method="post" action="#go" enctype="multipart/form-data" onsubmit="return users_forma_check()" >
    <h3>Upload your .csv file</h3>
    <input type="file" name="users_table" id="users_table_input" value="" /> <br><br>
    <input type="submit" class="button" value="Upload" />
</form>
