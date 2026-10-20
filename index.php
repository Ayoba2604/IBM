<?php
declare(strict_types=1);

// Convenience entry point: keep the application front controller inside public/.
header("Location: public/", true, 302);
exit();
