# Changelog:

## 1.3.0 - 2021-03-13

* Add support for PHP 8.0 and drop support for PHP 7.1.


## 1.2.0 - 2020-11-05

* Add properties to retrieve the executed command and working directory to the
  ProcessOutputInterface. These are here to make debugging failing commands in
  systems that execute commands concurrently.


## 1.1.0 - 2020-10-30

* Add mechanism to allow data to be passed into processes via STDIN
  (via the ProcessInterface::writeInput method). Thanks to DBX12 for this
  contribution.


## 1.0.0 - 2019-08-10

* First stable release.
