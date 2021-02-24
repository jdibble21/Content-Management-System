# User guide for Content Mangement System

## PHP methods
- Requirements: must use <code>ContentManagementLogic()</code> class to interact with system
- <code>CheckInputForProfanity($input)</code>: Takes a string of input and runs it against regex, blacklist and whitelist
to scan for profanic content. 
    - If profanity is detected, a <code>False</code> value is returned, and a reference to the post having profanity is added to the cms database
    - If no profanity is detected, a <code>True</code> value is returned.