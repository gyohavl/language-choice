<?php
include('../src/main.php');
if (!configExists()) {
    echo showConfigForm();
} else if (!dbReady()) {
    echo showDbSetup();
}