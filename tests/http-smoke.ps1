param([string]$BaseUrl='http://127.0.0.1:8090')
$ErrorActionPreference='Stop'
$expected=@{'/'=200;'/products'=200;'/cart'=200;'/login'=200;'/register'=200;'/about'=200;'/contact'=200;'/insurance'=200;'/legal'=200;'/legal/privacy-policy'=200;'/pharmacy-in-ganapathy-coimbatore'=200;'/sitemap.xml'=200;'/not-a-route'=404}
foreach($path in $expected.Keys){$response=Invoke-WebRequest -Uri ($BaseUrl+$path) -SkipHttpErrorCheck;if($response.StatusCode-ne$expected[$path]){throw "$path returned $($response.StatusCode)"};Write-Output "PASS $path $($response.StatusCode)"}
$search=Invoke-RestMethod -Uri ($BaseUrl+'/api/search?q=para');if(-not$search.matches){throw 'Search returned no matches'};Write-Output 'PASS search API'
