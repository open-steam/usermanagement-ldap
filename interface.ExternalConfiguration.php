<?php
	interface ExternalConfiguration{
		public function getRoot();
		public function getHost();
		public function getPort();
		public function issetSSL();
		public function getSystemLogin();
		public function getSystemPassword();
	}
?>