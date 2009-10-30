---
layout: post
title: "Copying files between S3 buckets"
excerpt: Using ruby and right_aws, you can easily duplicate Amazon S3 buckets.
---

The solution that I came up with for copying content from one S3 bucket to another was fairly easy to implement.
My solution heavily relies on a fantastic ruby library called [RightAws](http://rightscale.rubyforge.org/right_aws_gem_doc/).

{% highlight ruby %}
require 'rubygems'
require 'right_aws'

aws_access_key_id = 'PUT YOUR ACCESS KEY ID FROM AMAZON HERE'
aws_secret_access_key = 'PUT YOUR SECRET ACCESS KEY FROM AMAZON HERE'
target_bucket = 'BUCKET_COPYING_FROM'
destination_bucket = 'BUCKET_COPYING_TO'

s3 = RightAws::S3Interface.new(aws_access_key_id, aws_secret_access_key)

copied_keys = Array.new
s3.incrementally_list_bucket(destination_bucket) do |key_set| 
  copied_keys << key_set[:contents].map{|k| k[:key]}.flatten 
end
copied_keys.flatten!

s3.incrementally_list_bucket(target_bucket) do |key_set|
  key_set[:contents].each do |key|
    key = key[:key]
    if copied_keys.include?(key)
      puts "#{destination_bucket} #{key} already exists. Skipping..."
    else
      puts "Copying #{target_bucket} #{key}"

      retries=0
      begin
        s3.copy(target_bucket, key, destination_bucket)
      rescue Exception => e
        puts "cannot copy key, #{e.inspect}\nretrying #{retries} out of 10 times..."
        retries += 1
        retry if retries <= 10
      end
    end
  end
end
{% endhighlight %}

If you have access to an EC2 instance, which should be pretty easy to get, you can run the above script on it and you won't be charged for bandwidth. It is recommended that you take that approach.

This code was somewhat inspired by [ELCtech.com's](http://www.elctech.com) post entitled ["Copying files between S3 accounts"](http://www.elctech.com/tutorials/copying-files-between-s3-accounts). Just to be clear, ELCtech's solution is for copying bucket contents between multiple S3 accounts. The solution posted here is for copying bucket contents between one S3 account. 