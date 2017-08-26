# Inplace

This is a tool that will allow you to download specified files from an
existing Github repository and place them into your local project. This
is useful for adding a common CircleCI, license or README.md file from
a template repo.

To install:

    composer global require ssx/inplace ^0

Alternatively, if you would like the signed .phar version:

    wget https://ssx.io/inplace/releases/inplace.phar &&
    wget https://ssx.io/inplace/releases/inplace.phar.pubkey &&
    sudo chmod +x inplace.phar && mv inplace* /usr/local/bin


To run:

    inplace fetch https://github.com/ssx/inplace-demo

Behind the scenes, this will close the repository locally and then check
for the existence of a `.inplace.yml` file which denotes the files within
the repository to copy into the current directory.

This is the format of an `.inplace.yml` file:

```yaml
files:
  - circle.yml
  - .drone.yml
```


### License
This project is licensed under an Apache 2.0 license which you can find
[in this LICENSE](https://github.com/inplacecli/inplace/blob/master/LICENSE).


### Feedback
If you have any feedback, comments or suggestions, please feel free to open an
issue within this repository.


### Security
If you have security feedback, please contact me via email at
[security@ssx.email](security@ssx.email) and I'll usually respond as soon as possible.
 within the repository on [Github](https://github.com/ssx/inplace).
