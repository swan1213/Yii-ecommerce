<?php

$servername = "localhost";
$username = "root";
$password = "";
$db_name = 'elliot';

// Create connection
$conn = mysqli_connect($servername, $username, $password, $db_name);
//$pname = 'testone';
//$query_cat = "SELECT id FROM products WHERE product_name = '$pname'";
//$result = $conn->query($query_cat);
//
//echo '<pre>';
//print_r($result->fetch_assoc());
//die();
if (isset($_POST["submit"])) {
  if (isset($_FILES["csv"])) {

    $file_name = $_FILES['csv']['name'];
    $type = $_FILES['csv']['type'];
    $tmpName = $_FILES['csv']['tmp_name'];
    $info = pathinfo($file_name);
    if ($info['extension'] == 'csv') {
      //if file already exists
      $upld_path = "E:/wamp64/www/elliot-live/google_file/" . $file_name;
      //Store file in directory "temp" 
      $file_save = move_uploaded_file($tmpName, $upld_path);        //Get File Contents
      $csv_data = array_map('str_getcsv', file($upld_path));
      foreach ($csv_data as $csv_row) {
        $cat_id = $csv_row[0];
        if ($csv_row[1] != '' && $csv_row[2] == '' && $csv_row[3] == '' && $csv_row[4] == '' && $csv_row[5] == '' && $csv_row[6] == '') {
          $cat_name = $csv_row[1];
          $parent_cat_name = '';
          $parent_cat_id = 0;
        }
        if ($csv_row[1] != '' && $csv_row[2] != '' && $csv_row[3] == '' && $csv_row[4] == '' && $csv_row[5] == '' && $csv_row[6] == '') {
          $cat_name = $csv_row[2];
          $parent_cat_name = $csv_row[1];
        }
        if ($csv_row[1] != '' && $csv_row[2] != '' && $csv_row[3] != '' && $csv_row[4] == '' && $csv_row[5] == '' && $csv_row[6] == '') {
          $cat_name = $csv_row[3];
          $parent_cat_name = $csv_row[2];
        }
        if ($csv_row[1] != '' && $csv_row[2] != '' && $csv_row[3] != '' && $csv_row[4] != '' && $csv_row[5] == '' && $csv_row[6] == '') {
          $cat_name = $csv_row[4];
          $parent_cat_name = $csv_row[3];
        }
        if ($csv_row[1] != '' && $csv_row[2] != '' && $csv_row[3] != '' && $csv_row[4] != '' && $csv_row[5] != '' && $csv_row[6] == '') {
          $cat_name = $csv_row[5];
          $parent_cat_name = $csv_row[4];
        }
        if ($csv_row[1] != '' && $csv_row[2] != '' && $csv_row[3] != '' && $csv_row[4] != '' && $csv_row[5] != '' && $csv_row[6] != '') {
          $cat_name = $csv_row[6];
          $parent_cat_name = $csv_row[5];
        }
        
        if ($parent_cat_name != '') {
          $parent_cat_name =  mysqli_real_escape_string($conn,$parent_cat_name);          
          $query_cat = "SELECT category_ID FROM categories WHERE category_name = '$parent_cat_name'";
          $result_query_cat = $conn->query($query_cat);
          $row_result = $result_query_cat->fetch_assoc();
          $parent_cat_id = $row_result['category_ID'];
        }
        $cat_name =  mysqli_real_escape_string($conn,$cat_name);
        $created = date('Y-m-d h:i:s', time());

        $sql = "INSERT INTO categories(category_ID,category_name,parent_category_ID,created_at)
                VALUES ('$cat_id', '$cat_name', '$parent_cat_id','$created')";
        $result = $conn->query($sql);

        if ($result === TRUE) {
           echo "Row with Id $cat_id has been Imported";
        }
        else {
          echo "Error: " . $sql . "<br>" . $conn->error;
        }
 
        echo '<br>';
      }
      echo "All Rows Imported Successfully";
    }
  }
}