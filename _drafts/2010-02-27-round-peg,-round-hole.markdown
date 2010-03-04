---
layout: post
title: Round PEG, Round Hole
excerpt: Round PEG, Round Hole...
---
 
To parse plaintext, Cucumber uses Gherkin uses Treetop.

leex: tokenizes text
yecc: turns tokens into higher lever data structure.

## Parsing Ideas

### Recursive Descent
   - functions call other functions to recognize and consume input
   - backtrack on failure and try next option
 
### Predictive Recursive Descent
  - functions call other functions to recognize and consume input
  - Stream lookahead to determine which branch to take (firsts, follows)
  - Fail early, retry very little

### Packrat Parsers O(N)
  - Works and looks like a Recursive Descent Parser
  - Memoizes intermediate results - success and fail
  - Trades speed for memory consumption
  
  
http://github.com/seancribbs/neotoma