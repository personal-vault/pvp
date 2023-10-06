# ExifTool by Phil Harvey

The [ExifTool](https://exiftool.org/) is installed via Dockerfile.

To install it, ensure the following is in the Dockerfile:

```
RUN curl https://exiftool.org/Image-ExifTool-12.62.tar.gz -o /tmp/exiftool.tar.gz \
    && tar -xzf /tmp/exiftool.tar.gz -C /tmp \
    && cd /tmp/Image-ExifTool-12.62 \
    && perl Makefile.PL \
    && make \
    && make install \
    && rm -rf /tmp/exiftool.tar.gz /tmp/Image-ExifTool-12.62
```

## License

This is free software; you can redistribute it and/or modify it under the same terms as [Perl itself](http://dev.perl.org/licenses/).
