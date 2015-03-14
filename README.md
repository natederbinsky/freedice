# freedice
A web-based implementation of Liar's Dice that supports multi-player games. Communication is supported via e-mail and the interface has custom views for mobile devices and bots.

# Introduction #

The following are the basic steps to get started with freedice:

  1. Check out `trunk` to a PHP-enabled directory
  1. Create a new MySQL database (it is recommended not to use an existing db, as the DB schema does not use any sort of namespacing to avoid collisions with existing tables)
  1. Import the database schema in `common/private/db`
  1. In the `common/private/lib` directory, copy+paste all `*`-config.inc.php.default files to `*`-config.inc.php, open, and fill out relevant configuration-variable values (should be self-explanatory)
  1. Go to the URL and have fun!

# Useful Notes #

  * If e-mail communication is enabled for a game, all players will be spammed after every game action (assuming sendmail, or equivalent, is properly configured with PHP)
  * By setting the `blank` variable to `Y` for a page, a machine-readable (ini) format is supplied

# Acknowledgments #

  * I developed this Liar's Dice implementation while a grad student in [John Laird](http://ai.eecs.umich.edu/people/laird)'s research group at the University of Michigan. I also helped develop an [iOS app](https://itunes.apple.com/us/app/michigan-liars-dice/id562997948) based upon this game, which uses a [Soar](http://sitemaker.umich.edu/soar) agent for opponent(s).
