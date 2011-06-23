<?php
require('mailApi.php');
$MailApi = new MailApi();
$MailApi->setScope('widget');

echo('<a href="#" onclick="location.href = \'' . $MailApi->getLink() . '\'">Вход через Mail.ru</a>');

if (isset($_GET['code'])) {
	if ($MailApi->token($_GET['code'])) {
		$info = $MailApi->getMethod('users.getInfo', array('uids' => $MailApi->token->x_mailru_vid));
		print_r($info);
	}
}
?>