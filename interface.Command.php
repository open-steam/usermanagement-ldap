<?php
	interface Command{
	
		public function execute(Request $request, Response $response);
	}
?>