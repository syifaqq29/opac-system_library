<?PHP include 'config.php';
$id_book = $_GET['id_book'];
$result = mysqli_query($connect, "DELETE FROM borrowed WHERE id_book='$id_book'");
header("location:dashboard.php");
