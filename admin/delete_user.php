<?PHP include('C:\xampp\htdocs\opac-system_library\config.php');
$id_user = $_GET['id_user'];
$result = mysqli_query($connect, "DELETE FROM users WHERE id_user='$id_user'");
header("location:dashboard.php");
