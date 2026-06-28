<?php
// Environment-driven local configuration with deliberately inert defaults.
define("HOST", getenv("DB_HOST") ?: "127.0.0.1");
define("USER", getenv("DB_USER") ?: "showcase_user");
define("PASSWORD", getenv("DB_PASSWORD") ?: "change-me");
define("DATABASE", getenv("DB_NAME") ?: "specialty_food_store");
define("CAN_REGISTER", "any");
define("DEFAULT_ROLE", "member");
