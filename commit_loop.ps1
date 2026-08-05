for ($i = 1; $i -le 21; $i++) {
    Add-Content -Path "commit_test.txt" -Value " " -NoNewline
    git add commit_test.txt
    git commit -m "Add space (commit $i.1)"
    
    Set-Content -Path "commit_test.txt" -Value "" -NoNewline
    git add commit_test.txt
    git commit -m "Remove space (commit $i.2)"
}  