try
    do shell script "open -n -a 'Google Chrome' --args '--app=http://teamhub.atwebpages.com/'"
on error
    do shell script "open http://teamhub.atwebpages.com/"
end try
