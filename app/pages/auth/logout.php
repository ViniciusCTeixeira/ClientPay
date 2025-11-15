<?php
Auth::logout();
header('Location: ?p=auth/login');
