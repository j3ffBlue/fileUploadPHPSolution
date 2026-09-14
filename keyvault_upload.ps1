param (
   [Parameter(Mandatory = $true)]
   [string]$certfilename,
   [Parameter(Mandatory = $true)]
   [string]$vaultname,
   [Parameter(Mandatory = $true)]
   [string]$pfxpassword
   #[Parameter(Mandatory = $true)]
   #[string]$basedir

)
Disable-AzContextAutosave -Scope Process

$TenantId = "0caa5102-88d1-445a-a834-d24f7daeeee3"
$ApplicationId = "01f9aac8-e0e5-469b-889a-d9c164106fca"
$ClientSecret = "v1J8Q~g~RxidLQQSueAS_il7oZdkF-q.c0rLmcUk"

$SecurePassword = ConvertTo-SecureString -String $ClientSecret -AsPlainText -Force
$Credential = New-Object -TypeName System.Management.Automation.PSCredential -ArgumentList $ApplicationId, $SecurePassword
# ==========================================


$targetfile = Join-Path -Path 'C:\\xampp\\htdocs\\webdev\\certificates' -ChildPath $certfilename
write-output($targetfile)
if (-not (Test-Path -Path $targetfile)) {
    Write-Error "File not found in IIS tempdir!"
    exit 1
}


Connect-AzAccount -ServicePrincipal -Tenant $TenantId -Credential $Credential
Write-Output('Login successfully!')
# Set-AzContext -SubscriptionName $subscription -Tenant $TenantId
$certname = $($certfilename.Replace('.pfx','')).Replace('.','-')
write-output("Generated certificate name: $certname")
$Password = ConvertTo-SecureString -String $pfxpassword -AsPlainText -Force
# ================================================

try {
    Import-AzKeyVaultCertificate -VaultName $vaultname -Name $certname -FilePath $targetfile -Password $Password 
} catch {
    Write-Error "Import failed: $($_.Exception.Message)"
}
# Let do remove temp cert in the IISserver
if (Test-Path -Path $targetfile) {
    try {
    Remove-Item -Path $targetfile -Force
    } catch {
        Write-Warning "u-n-a-b-l-e to remove uploaded certfile in the webserver! ... "
    }
}