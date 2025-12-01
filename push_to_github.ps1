# push_to_github.ps1
# Usage (PowerShell):
# 1) Open PowerShell in project root (d:\BanNoiThat\HKT)
# 2) Run: .\push_to_github.ps1
# When prompted to push, Git will ask for credentials. Use your GitHub username and a Personal Access Token (PAT) as the password.

param(
    [string]$RemoteUrl = 'https://github.com/MinhKhoi07/Website_Ban_noi_that_sdAPI.git',
    [string]$UserName = 'MinhKhoi07',
    [string]$UserEmail = 'khoiminh.071204@gmail.com',
    [string]$Branch = 'main'
)

Write-Host "Running push_to_github.ps1"
Write-Host "Remote: $RemoteUrl"

# 1) Configure git user
git config user.name "$UserName"
git config user.email "$UserEmail"

# 2) Init repo if .git not present
if (-not (Test-Path -Path .git)) {
    Write-Host "No .git directory found — initializing repository..."
    git init
} else {
    Write-Host ".git directory found — using existing repo"
}

# 3) Add all changes
Write-Host "Staging all files..."
git add -A

# 4) Commit (allow no-op if nothing to commit)
try {
    git commit -m "Initial commit: upload project" -q
    Write-Host "Committed changes"
} catch {
    Write-Host "No changes to commit or commit failed — continuing"
}

# 5) Set remote (replace if exists)
$exists = git remote | Select-String -Pattern "origin" -Quiet
if ($exists) {
    Write-Host "Remote 'origin' exists — updating URL to $RemoteUrl"
    git remote set-url origin $RemoteUrl
} else {
    Write-Host "Adding remote origin -> $RemoteUrl"
    git remote add origin $RemoteUrl
}

# 6) Ensure branch name
Write-Host "Setting branch to $Branch"
git branch -M $Branch

Write-Host "About to push to origin/$Branch. You will be prompted for credentials if needed."
Write-Host "If using HTTPS, use your GitHub username and a Personal Access Token (PAT) as password."

# Optional: advise credential manager
Write-Host "If you want to cache credentials on Windows, run: git config --global credential.helper manager-core"

# 7) Push
try {
    git push -u origin $Branch
    Write-Host "Push completed"
} catch {
    Write-Host "Push failed: $_"
    Write-Host "If authentication fails, create a Personal Access Token (PAT) on GitHub with 'repo' scope and use it as password."
}

Write-Host "Done."
