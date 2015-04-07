<?php
global $CONFIG;
template_Header('Welcome');
displayTemplate('Welcome');
if ($CONFIG['self-registration'])
	displayTemplate('WelcomeRegister');
template_Footer();
