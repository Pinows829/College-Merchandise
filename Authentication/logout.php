<?php
include '../Includes/db.php';
session_destroy();
header("Location: ".BASE."/auth/login.php");