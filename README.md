# Inplace

This is a tool that will allow you to download specified files from an
existing Github repository and place them into your local project. This 
is useful for adding a common CircleCI, license or README.md file from 
a template repo.

To install:

    wget https://ssx.io/inplace/inplace.phar && \ 
    wget https://ssx.io/inplace/inplace.phar.pubkey && \
    sudo mv inplace.phar.* /usr/local/bin

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

This project is licensed under an Apache 2.0 license which you can find within
this repository in the [LICENSE file](https://github.com/ssx/inplace/blob/master/LICENSE).


### Feedback

If you have any feedback, comments or suggestions, please feel free to open an
issue within the repository on [Github](https://github.com/ssx/inplace).