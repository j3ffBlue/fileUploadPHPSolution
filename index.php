<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>under development</title>
    <style>
        textarea {
            width: 50%; 
            height: 200px; 
            background-color: #f0f0f0;
            color: #333;
        }
</style>
</head>    
<body>
    <img src="./images/Azure-cert-management.png" alt="webIcon" style="width:100%;height: 128px;">
    <hr>
    <fieldset>
        <div>
            <p>Accept certficate in PFX format only.</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                <div>
                  <label for="file-upload">Choose a certificate to upload:</label><br><br>
                  <input type="file" id="file-upload" name="uploaded_file" required><br><br>
                </div>
                <div>
                    <label for="passphrase">Enter passphrase:</label>
                    <input type="password" id="passphrase" name="passphrase" placeholder="Enter password" required>
                    <br> 
                </div><hr>
                <div>
                    <label for="subscription">Choose Subscription:</label>
                    <select id="subs" required>
                        <option value="">---</option>
                        <option value="bsc_npn">bsc-npn</option>
                        <option value="bsc_pn">bsc-pn</option>
                        <option value="bsc_pp">bsc-pp</option>
                        <option value="bsc_npp">bsc-npp</option>
                        <option value="home">test</option>
                    </select>
                    <br><br>
                </div>
                <div>
                    <!-- second dropdown -->
                  <select id="kv" name="kv" disabled required>   
                     <option value="">-- select keyvault --</option>
                  </select>   

                </div>
                <br>
                <button type="submit" id="submitBtn" style="background-color: rgba(4,131,250,0.973); color: white">upload</button>
            </form>
        </div>
        <h4 hidden id="atwork">Please wait. In progress ....</h4>
    </fieldset>
    
    <script>
      if (window.history.replaceState) {
       window.history.replaceState(null, null, window.location.href);
      }
    </script>
    <script>
        const optionsMap = {
            bsc_npn: ["kv-nw-npn-ics-sc-cus-22","kv-nw-npn-ics-sc-eus2-22"],
            bsc_pn: ["kv-nw-pn-ics-sc-cus-22","kv-nw-pn-ics-sc-eus2-22"],
            bsc_npp: ["kv-nw-npp-ics-sc-cus-22","kv-nw-npp-ics-sc-eus2-22"],
            bsc_pp: ["kv-nw-pp-ics-sc-cus-22","kv-nw-pp-ics-sc-eus2-22"],
            home: ["kv-nw-pn-sc-ics-cus-007"]
        };
        const categorySelect = document.getElementById("subs");
        const itemSelect = document.getElementById("kv");

        categorySelect.addEventListener("change", function () {
            const selectedCategory = this.value;
            itemSelect.innerHTML = '<option value="">-- select keyvault --</option>';
            if (selectedCategory && optionsMap[selectedCategory]) { 
                optionsMap[selectedCategory].forEach(item => {
                    const option = document.createElement("option");
                    option.value = item.toLowerCase();
                    option.textContent = item;
                    itemSelect.appendChild(option);
                });
                itemSelect.disabled = false;
            } else {
                itemSelect.disabled = true;
            }
        });
    </script>
    
    <br>
    <textarea>
    <?php
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $file = $_FILES['uploaded_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileError   = $file['error'];
        $allowedExtensions = ['pfx'];
        $passkey = $_POST['passphrase'];
        $keyvault = $_POST['kv'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // =============================================
        $psScriptPath = "C:\\xampp\\htdocs\\webdev\\scripts\\keyvault_upload.ps1";
        $param1 = $fileName;
        $param2 = $keyvault; 
        $param3 = $passkey;
       

        if (!in_array($fileExt, $allowedExtensions)) {
            echo "Error: Invalid file type. Only PFX were allowed.";
        } elseif ($fileError !== 0) {
            echo "Error: There was a problem uploading your file (Code: $fileError).";
        } else {
            //$newFileName = uniqid('', true) . "." . $fileExt;
            
            $uploadDirectory = "./certificates/";
            //$targetPath = $uploadDirectory . $newFileName;
            $targetPath = $uploadDirectory . $fileName;

            if (move_uploaded_file($fileTmpName, $targetPath)) {
                //echo "Success! Your file was uploaded as: " . htmlspecialchars($newFileName) ."\t";
                echo "Success! Your file was uploaded as: " . htmlspecialchars($fileName) ."\t";
                $cmd = "openssl pkcs12 -info -in $targetPath -password pass:$passkey -nokeys";
                exec($cmd, $output, $return_var);
                if (count($output) == 0) {
                    echo "\nError: Invalid Password! Can't continue ...";
                } else {
                    echo "\nVerify certificate password --- VALID!";
                    echo "\nKey vault upload initiate ........ to $keyvault";
                    $command = "pwsh -NoProfile -ExecutionPolicy Bypass -File \"$psScriptPath\" -certfilename $param1 -vaultname $param2 -pfxpassword $param3 2>&1";
                    //echo "\n$command";
                    $output = exec($command, $outputArray, $returnVar);
                    echo "\nReturn Code: $returnVar";
                    echo "\nOutput: " . implode("\n", $outputArray);
                    if ($return_var == 0) {
                        echo "\n--- UPLOADED SUCCESSFULLY ---";
                        
                    } else {
                        echo "\nFAILED To UPLOAD CERTIFICATE!";
                    };
                };
                
            } else {
                echo "Error: Could not move the file to the destination folder. Check folder permissions.";
            };
            
        }
        
      } else {
            echo "-->>"; 
      }
    ?>
    </textarea>
    
    <script>
        const btn = document.getElementById('submitBtn');
        const prog = document.getElementById('atwork');
        btn.addEventListener('click', function onClick() {
            btn.hidden = true;
            prog.hidden = false;
        });
    </script>
    
</body>
</html>