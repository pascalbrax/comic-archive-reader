comic-archive-reader
====================

This project grew out of an irratation I had with my options for viewing comic archives. 

There are plenty of native apps for different platforms to view .cbr and .cbz collections, but nothing web based and therefore easily accessible from anywhere.

I used jquery and prettyPhoto to make the interface look nice when moving through comic images, and the backend is simple PHP that scans directories to build series and issue lists.

The only steps necessary to make this work are to create directories in the "comics" folder named after your various comic series, and then upload your .cbr and .cbz files into those names directories. The application will dynamically unarchive them into temporary storage for the purposes of viewing them.

Note that your .cbz files need to be .zip files at heart, and your .cbr files need to be .rar files at heart. If you aren't familiar with these file formats you can research them online to find out what that means. It's important in the unarchiving process.

Lastly, I've included a compiled version of the linux program "unrar" that may need execute permissions before .cbr files will work. The native program "unzip" is used for .cbz and should be supported out of the box.
