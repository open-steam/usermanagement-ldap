<?php
	interface CommandResolver{
	
		public function getCommand(Request $request);
	}
?>