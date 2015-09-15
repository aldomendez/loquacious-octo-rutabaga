<?php

/* * * * * * * * * * * * * * * * * *
 *
 * <- Backend for Cenny.js ->
 *
 * How data is stored:
 *   Backend (root) directory
 *      v
 *    Group directory
 *      v
 *     User directory
 *      v
 *      Data.txt
 *
 *
 /* * * * * * * * * * * * * * * * * */

function is_assoc($var) {
	return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
}

function openFile($url, $size) {
	if (file_exists($url)) {
		$file = $url;
		$dataToReturn = file_get_contents($file);
	} else {
		saveFile($url, "");
		$dataToReturn = "";
	}
	return $dataToReturn;

}

function genToken() {
	$length = 67;
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890-.!,";
	//length:36
	$final_rand = '';
	for ($i = 0; $i < $length; $i++) {
		$final_rand .= $chars[rand(0, strlen($chars) - 1)];

	}
	return $final_rand;
}

function delete_files($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . "/" . $object) == "dir")
					rrmdir($dir . "/" . $object);
				else
					unlink($dir . "/" . $object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function splitString($stringX, $by) {
	$arrayX = explode($by, $stringX);
	return $arrayX;
}

function saveFile($url, $dataToSave) {
	$file = $url;
	if (strlen($dataToSave) < 1948576) { //maxiumum file size
		file_put_contents($file, $dataToSave);
	} else {
		echo '{"cenError":"not enough available storage"}';
	}
}

function addToFile($url, $dataToSave) {
	$myFile = $url;
	$fh = fopen($myFile, 'a') or die($myFile);
	fwrite($fh, $dataToSave);
	fclose($fh);

}

//***********************************************************
$group_loggedin = false;
$user_loggedin = false;
//***********************************************************

$groupName = $_POST['groupName'];
$groupKey = $_POST['groupKey'];

$userName = $_POST['userName'];
$userPass = $_POST['userPass'];

$directory = "backend";
if (file_exists("backend/")) {
} else {
	$oldmask = umask(0);
	mkdir("backend/", 0777);
	umask($oldmask);

}

//make sure groupName & userName are valid
$userNameValid = false;
$groupNameValid = false;
$userPassValid = true;


//pass
if (strlen($userPass) > 70) {
	$userPassValid = false;
} else if (strlen($userPass) < 5) {
	$userPassValid = false;
}

//username
if (preg_match('/^\w{5,}$/', $userName)) {
	$userNameValid = true;
}
if (strlen($userName) > 22) {
	$userNameValid = false;
}

//group
if (preg_match('/^\w{5,}$/', $groupName)) {
	$groupNameValid = true;
}
if (strlen($groupName) > 22) {
	$groupNameValid = false;
}

//continue only if username / groupname valid
if ($groupNameValid === true && $userNameValid === true && $userPassValid === true) {

	// groups

	if (file_exists("$directory/$groupName/")) {

		$orginalKey = openFile("$directory/$groupName/key.txt", 10000);
		if ($orginalKey === $groupKey) {
			$group_loggedin = true;
		} else {
			echo '{"cenError":"group key incorrect"}';
		}

	} else {
		$oldmask = umask(0);
		mkdir("$directory/$groupName/", 0777);
		umask($oldmask);
		saveFile("$directory/$groupName/key.txt", $groupKey);
		$group_loggedin = true;
	}

	// users

	if (file_exists("$directory/$groupName/$userName/")) {
		$orginalPass = openFile("$directory/$groupName/$userName/pass.txt", 10000);
		$token = openFile("$directory/$groupName/$userName/token.txt", 10000);
		if ($orginalPass === $userPass) {
			$user_loggedin = true;
		} else if ($token !== "") {
			if ($token === $userPass) {
				$user_loggedin = true;
			} else {
				echo '{"cenError":"user incorrect"}';
			}
		} else {
			echo '{"cenError":"user incorrect"}';
		}

	} else if ($userName === "default") {
		$oldmask = umask(0);
		mkdir("$directory/$groupName/default/", 0777);
		umask($oldmask);
		saveFile("$directory/$groupName/default/pass.txt", "default");
		addToFile("$directory/$groupName/userlist.txt", 'default' . "@SEPCENNYUSER@");
		$user_loggedin = true;
	} else {
		echo '{"cenError":"user does not exist"}';
	}

	//########################################################################################################################
	if ($group_loggedin === true && $user_loggedin === true) {

		//**********************************************
		//get actions / data from request
		$action = $_POST['action'];
		if (isset($_POST['data'])) {
			$clientData = $_POST['data'];
			//formatting
			$clientData = str_replace("\'", "'", $clientData);
			$clientData = str_replace('\"', '"', $clientData);
		}
		//**********************************************

		// - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - /
		if ($action === "get") {

			$openedData = openFile("$directory/$groupName/$userName/data.txt", 500000);
			$getProperties = $_POST['getProperties'];
			if ($getProperties === "all") {

				if ($openedData !== "") {
					echo $openedData;
				} else {
					echo '{"cenError":"user is empty"}';
				}

			} else {
				$getProperties = splitString($getProperties, "@n@");
				if ($openedData !== "") {
					$openedData = json_decode($openedData);
					$outputObj = array();

					foreach ($openedData as $pName => $pData) {
						for ($x = 0; $x < count($getProperties); $x++) {
							if ($pName === $getProperties[$x]) {
								$outputObj[$pName] = $pData;
							}
						}
					}

					$outputObj = json_encode($outputObj);
					//encode output obj to json
					echo $outputObj;

				} else {//opened data is empty
					echo '{"cenError":"user is empty"}';
				}

			}

		} else if ($action === "generateAuthToken") {
			if ($userName !== "default") {
				$token = genToken();
				saveFile("$directory/$groupName/$userName/token.txt", $token);
				echo json_encode($token);
			}

		} else if ($action === "changePass") {
			if ($userName !== "default") {
				$newPass = $_POST['newPass'];
				saveFile("$directory/$groupName/$userName/pass.txt", $newPass);
				echo '{"cenInfo":"password changed"}';
			} else {
				echo '{"cenError":"cannot modify default user password"}';
			}
		} else if ($action === "getOther") {

			$otherUser = $_POST['otherUser'];

			if (file_exists("$directory/$groupName/$otherUser/")) {
				$openedRead = openFile("$directory/$groupName/$otherUser/read.txt", 1000);
				$arrayX = splitString($openedRead, "@n@");
				$userFoundInP = false;
				for ($x = 0; $x < count($arrayX); $x++) {//look for current user in $otherUser's perms
					if ($arrayX[$x] === $userName) {
						$userFoundInP = true;
					}
				}
				if ($userFoundInP === true || $openedRead === "allowAll") {
					$openedData = openFile("$directory/$groupName/$otherUser/data.txt", 500000);
					if ($openedData !== "") {
						echo $openedData;
					} else {
						echo '{"cenError":"user is empty"}';
					}
				} else {//check if any property allows $userName read access
					$openedPropertyPerm = openFile("$directory/$groupName/$otherUser/propertyPerm.txt", 50000);
					$openedData = openFile("$directory/$groupName/$otherUser/data.txt", 500000);
					//actual data
					$openedData = json_decode($openedData);
					$permissionProperties = json_decode($openedPropertyPerm);

					$isThere = false;
					if ($permissionProperties !== "") {//make sure permissions are set

						$outputObj = array();
						//create obj for properties user is given access to.

						foreach ($permissionProperties as $propertyName => $propertyValue) {
							$propertyUsers = splitString($propertyValue, "@n@");
							for ($x = 0; $x < count($propertyUsers); $x++) {//look for current user in $otherUser's perms
								if ($propertyUsers[$x] === $userName) {

									$isThere = true;
									foreach ($openedData as $dataPropertyName => $dataPropertyData) {
										if ($propertyName === $dataPropertyName) {
											$outputObj[$propertyName] = $dataPropertyData;
										}
									}

								} else if ($propertyValue === "allowAll") {
									$isThere = true;
									foreach ($openedData as $dataPropertyName => $dataPropertyData) {
										if ($propertyName === $dataPropertyName) {
											$outputObj[$propertyName] = $dataPropertyData;
										}
									}
								}
							}

						}

					} else {//permissions are not set
						//do nothing
					}

					if ($isThere === true) {//if $userName is found in properties do this
						$outputObj = json_encode($outputObj);
						//encode output obj to json
						echo $outputObj;
					} else {//otherwise, present error
						echo '{"cenError":"access not granted"}';
					}

				}
			} else {
				echo '{"cenError":"user does not exist"}';
			}

		} else if ($action === "getEmailOther") {

			$otherUser = $_POST['otherUser'];

			if (file_exists("$directory/$groupName/$otherUser/")) {
				$openedRead = openFile("$directory/$groupName/$otherUser/emailRead.txt", 1000);
				$arrayX = splitString($openedRead, "@n@");
				$userFoundInP = false;
				for ($x = 0; $x < count($arrayX); $x++) {//look for current user in $otherUser's perms
					if ($arrayX[$x] === $userName) {
						$userFoundInP = true;
					}
				}
				if ($userFoundInP === true || $openedRead === "allowAll" || $otherUser === $userName || $openedRead === "") {
					$openedEmail = openFile("$directory/$groupName/$otherUser/email.txt", 1000);
					if ($openedEmail !== "") {
						echo json_encode($openedEmail);
					} else {
						echo '{"cenError":"email not set."}';
					}
				} else {
					echo '{"cenError":"email access not granted."}';
				}
			} else {
				echo '{"cenError":"user "' . $otherUser . '" does not exist."}';
			}

		} else if ($action === "setOther") {

			$otherUser = $_POST['otherUser'];

			if (file_exists("$directory/$groupName/$otherUser/")) {
				$openedWrite = openFile("$directory/$groupName/$otherUser/write.txt", 1000);
				$arrayX = splitString($openedWrite, "@n@");
				$userFoundInP = false;
				for ($x = 0; $x < count($arrayX); $x++) {//look for current user in $otherUser's perms
					if ($arrayX[$x] === $userName) {
						$userFoundInP = true;
					}
				}
				if ($userFoundInP === true || $openedWrite === "allowAll") {
					saveFile("$directory/$groupName/$otherUser/data.txt", $clientData);
					echo '{"cenInfo":"set"}';
				} else {
					echo '{"cenError":"write access not granted."}';
				}
			} else {
				echo '{"cenError":"user does not exist."}';
			}

		} else if ($action === "updateOther") {

			$otherUser = $_POST['otherUser'];

			if (file_exists("$directory/$groupName/$otherUser/")) {
				$openedWrite = openFile("$directory/$groupName/$otherUser/write.txt", 1000);
				$arrayX = splitString($openedWrite, "@n@");
				$userFoundInP = false;
				for ($x = 0; $x < count($arrayX); $x++) {//look for current user in $otherUser's perms
					if ($arrayX[$x] === $userName) {
						$userFoundInP = true;
					}
				}
				if ($userFoundInP === true || $openedWrite === "allowAll") {
					$openedData = openFile("$directory/$groupName/$otherUser/data.txt", 500000);
					if ($openedData === "") {
						$openedData = array();
					} else {
						$openedData = json_decode($openedData);
					}

					$clientData = json_decode($clientData);

					$outputData = array();
					$markedFDProps = array();

					foreach ($openedData as $opName => $opData) {

						//check if should be deleted
						$shouldBeDeleted = false;
						foreach ($clientData as $cpName => $cpData) {
							if ($cpName === "DELETE") {
								for ($i = 0; $i < count($cpData); $i++) {//go though 'DELETE' array
									if ($cpData[$i] === $opName) {//mark to be deleted
										$shouldBeDeleted = true;
									}
								}//end 'DELETE' array search
							}
						}//end $clientData foreach
						if ($souldBeDeleted === false) {//only push to output object if it should stay
							$outputData[$opName] = $opData;
						}
					}//end $openData foreach

					foreach ($clientData as $cpName => $cpData) {//push all clientData (from cenny.js) properties to output object
						if ($cpName !== 'DELETE') {//do not store 'DELETE' property
							$outputData[$cpName] = $cpData;
						}
					}
					saveFile("$directory/$groupName/$otherUser/data.txt", json_encode($outputData));

					echo '{"cenInfo":"updated"}';

				} else {
					echo '{"cenError":"write access not granted."}';
				}
			} else {
				echo '{"cenError":"user does not exist."}';
			}

		} else if ($action === "update") {

			$openedData = openFile("$directory/$groupName/$userName/data.txt", 500000);
			if ($openedData === "") {
				$openedData = array();
			} else {
				$openedData = json_decode($openedData);
			}
			
			

			$clientData = json_decode($clientData);

			$outputData = array();
			$markedFDProps = array();

			foreach ($openedData as $opName => $opData) {

				//check if should be deleted
				$shouldBeDeleted = false;
				foreach ($clientData as $cpName => $cpData) {
					if ($cpName === "DELETE") {
						for ($i = 0; $i < count($cpData); $i++) {//go though 'DELETE' array
							if ($cpData[$i] === $opName) {//mark to be deleted
								$shouldBeDeleted = true;
							}
						}//end 'DELETE' array search
					}
				}//end $clientData foreach
				if ($shouldBeDeleted === false) {//only push to output object if it should stay
					$outputData[$opName] = $opData;
				}
			}//end $openData foreach

			foreach ($clientData as $cpName => $cpData) {//push all clientData (from cenny.js) properties to output object
				if ($cpName !== 'DELETE') {//do not store 'DELETE' property
					$outputData[$cpName] = $cpData;
				}
			}
			saveFile("$directory/$groupName/$userName/data.txt", json_encode($outputData));

			echo '{"cenInfo":"updated"}';

		} else if ($action === "set") {

			saveFile("$directory/$groupName/$userName/data.txt", $clientData);
			echo '{"cenInfo":"set"}';

		} else if ($action === "permissions") {
			if ($userName !== "default") {//make sure no permissions are set on default user
				$read = $_POST['read'];
				$write = $_POST['write'];
				$emailRead = $_POST['emailRead'];
				$offlinePerm = $_POST['offlinePerm'];
				$propertyObj = $_POST['propertyObj'];
				//for specific properties

				if ($read !== "DoNotEdit") {
					saveFile("$directory/$groupName/$userName/read.txt", $read);
				}
				if ($write !== "DoNotEdit") {
					saveFile("$directory/$groupName/$userName/write.txt", $write);
				}
				if ($emailRead !== "DoNotEdit") {
					saveFile("$directory/$groupName/$userName/emailRead.txt", $emailRead);
				}
				if ($offlinePerm !== "DoNotEdit") {
					saveFile("$directory/$groupName/$userName/offlinePerm.txt", $offlinePerm);
				}
				if ($propertyObj !== "DoNotEdit") {//updates permissions on properties
					//clean up property obj
					$propertyObj = str_replace("\'", "'", $propertyObj);
					$propertyObj = str_replace('\"', '"', $propertyObj);

					$unpackedPropertyObj = json_decode($propertyObj);

					$openedPermProperties = openFile("$directory/$groupName/$userName/propertyPerm.txt", 50000);
					$openedPermProperties = json_decode($openedPermProperties);

					$outputPermProperties = array();

					foreach ($unpackedPropertyObj as $pName => $pData) {
						foreach ($openedPermProperties as $ppName => $ppData) {
							if ($pName === $ppName) {
								$outputPermProperties[$ppName] = $pData;
							} else {
								$outputPermProperties[$pName] = $pData;
							}
							$outputPermProperties[$ppName] = $ppData;
						}
					}

					saveFile("$directory/$groupName/$userName/propertyPerm.txt", json_encode($outputPermProperties));
				}
				echo '{"cenInfo":"permissions updated"}';
			} else {
				echo '{"cenError":"permissions cannot be edited on default user"}';
			}

		} else if ($action === "getOfflinePerm") {
			$openedPerm = openFile("$directory/$groupName/$userName/offlinePerm.txt", 50000);
			echo json_encode($openedPerm);
		} else if ($action === "removeUser") {
			
			$userToRemovePass = openFile("$directory/$groupName/$userName/pass.txt", 50000);
			if ($userToRemovePass === $clientData) {
			//remove user directory
			delete_files("$directory/$groupName/$userName/");

			//remove from user list
			$users = openFile("$directory/$groupName/userlist.txt", 500000);
			$users = str_replace("$userName@SEPCENNYUSER@", "", $users);
			saveFile("$directory/$groupName/userlist.txt", $users);

			echo json_encode("removed user " . $userName . " in group " . $groupName);
			} else {
				echo '{"cenError":"user pass incorrect"}';
			}

		} else if ($action === "setEmail") {

			saveFile("$directory/$groupName/$userName/email.txt", $clientData);

		} else if ($action === "userExists") {
			if (file_exists("$directory/$groupName/$clientData/")) {
				echo 'true';
			} else {
				echo 'false';
			}
		} else if ($action === "createuser") {
			$info = json_decode($clientData);

			foreach ($info as $pName => $pData) {
				if ($pName === "username") {
					$newUsername = $pData;
				} else if ($pName === "password") {
					$newPassword = $pData;
				}
			}

			if (file_exists("$directory/$groupName/$newUsername/")) {
				echo '{"cenError":"user ' . $newUsername . ' already exists"}';
			} else {

				$newPasswordValid = true;
				$newUsernameValid = false;

				//pass
				if (strlen($newPassword) > 70) {
					$newPasswordValid = false;
				} else if (strlen($newPassword) < 5) {
					$newPasswordValid = false;
				}

				//username
				if (preg_match('/^\w{5,}$/', $newUsername)) {
					$newUsernameValid = true;
				}
				if (strlen($newUsername) > 22) {
					$newUsernameValid = false;
				}

				if ($newPasswordValid === true) {
					if ($newUsernameValid === true) {
						$oldmask = umask(0);
						mkdir("$directory/$groupName/$newUsername/", 0777);
						umask($oldmask);
						saveFile("$directory/$groupName/$newUsername/pass.txt", $newPassword);
						addToFile("$directory/$groupName/userlist.txt", $newUsername . "@SEPCENNYUSER@");
						echo '{"info":"user created"}';
					} else {
						echo '{"cenError":"username invalid"}';
					}
				} else {
					echo '{"cenError":"password invalid"}';
				}
			}

		} else {
			echo '{"cenError":"' . $action . ' is not a valid action"}';
		}
		// - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - / - /

	}//close authentication if statement
	//########################################################################################################################

} else {// userName or groupName invalid
	echo '{"cenError":"username or groupname invalid"}';
}

//for debugging
$requests = openFile("$directory/requests.txt", 40000);
$requests++;
saveFile("$directory/requests.txt", $requests);
?>
