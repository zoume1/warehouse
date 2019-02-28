<?php

class testLongJob {
	public function perform() {
		echo 'i am going to sleep ';
		sleep(600);
		echo 'hello world';
		//exit(1);
	}

}

