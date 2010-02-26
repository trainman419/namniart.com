---
layout: post
title: "Exploring Ruby: Arrays - the zip method"
excerpt: "Exploring Ruby: Arrays - the zip method"
---

Here is an exploration for an instance method named "zip". The following is what we
find.

We begin by opening the ruby source. Naturally, we'll find the zip method in the array.c file. The method that we are looking for is:

*rb_ary_zip*

At this point, we can read the documentation and see what this method actually does:

{% highlight text %}
  /*
   *  call-seq:
   *     array.zip(arg, ...)                   -> an_array
   *     array.zip(arg, ...) {| arr | block }  -> nil
   *
   *  Converts any arguments to arrays, then merges elements of
   *  <i>self</i> with corresponding elements from each argument. This
   *  generates a sequence of <code>self.size</code> <em>n</em>-element
   *  arrays, where <em>n</em> is one more that the count of arguments. If
   *  the size of any argument is less than <code>enumObj.size</code>,
   *  <code>nil</code> values are supplied. If a block given, it is
   *  invoked for each output array, otherwise an array of arrays is
   *  returned.
   *
   *     a = [ 4, 5, 6 ]
   *     b = [ 7, 8, 9 ]
   *     [1,2,3].zip(a, b)      #=> [[1, 4, 7], [2, 5, 8], [3, 6, 9]]
   *     [1,2].zip(a,b)         #=> [[1, 4, 7], [2, 5, 8]]
   *     a.zip([1,2],[8])       #=> [[4,1,8], [5,2,nil], [6,nil,nil]]
   */
{% endhighlight %}

At first glance, that sounds heavy. Let's try to break it down...

Scratch that! Heard of rubinius? Let's check that out and see what sort of specs are written for the zip method. 

Real quick, rubinius is a ruby virtual machine implemented in ruby. It comes with specs, so we can look at those.

These are the #zip method specs found in 

*zip_spec.rb*

{% highlight ruby %}
  
describe "Array#zip" do
  it "returns an array of arrays containing corresponding elements of each array" do
    [1, 2, 3, 4].zip(["a", "b", "c", "d", "e"]).should ==
      [[1, "a"], [2, "b"], [3, "c"], [4, "d"]]
  end

  it "fills in missing values with nil" do
    [1, 2, 3, 4, 5].zip(["a", "b", "c", "d"]).should ==
      [[1, "a"], [2, "b"], [3, "c"], [4, "d"], [5, nil]]
  end

  it "properly handles recursive arrays" do
    a = []; a << a
    b = [1]; b << b

    a.zip(a).should == [ [a[0], a[0]] ]
    a.zip(b).should == [ [a[0], b[0]] ]
    b.zip(a).should == [ [b[0], a[0]], [b[1], a[1]] ]
    b.zip(b).should == [ [b[0], b[0]], [b[1], b[1]] ]
  end

  it "calls #to_ary to convert the argument to an Array" do
    obj = mock('[3,4]')
    obj.should_receive(:to_ary).and_return([3, 4])
    [1, 2].zip(obj).should == [[1, 3], [2, 4]]
  end

  it "calls block if supplied" do
    values = []
    [1, 2, 3, 4].zip(["a", "b", "c", "d", "e"]) { |value|
      values << value
    }.should == nil

    values.should == [[1, "a"], [2, "b"], [3, "c"], [4, "d"]]
  end

  it "does not return subclass instance on Array subclasses" do
    ArraySpecs::MyArray[1, 2, 3].zip(["a", "b"]).should be_kind_of(Array)
  end
end


{% endhighlight %}
Thanks to Brian Ford for the specs that he wrote.

It may be interesting to look at the c implementation and the rubinius implementation.

*C Implementation*
{% highlight c %}
static VALUE
rb_ary_zip(int argc, VALUE *argv, VALUE ary)
{
    int i, j;
    long len;
    VALUE result = Qnil;

    len = RARRAY_LEN(ary);
    for (i=0; i<argc; i++) {
	argv[i] = take_items(argv[i], len);
    }
    if (!rb_block_given_p()) {
	result = rb_ary_new2(len);
    }

    for (i=0; i<RARRAY_LEN(ary); i++) {
	VALUE tmp = rb_ary_new2(argc+1);

	rb_ary_push(tmp, rb_ary_elt(ary, i));
	for (j=0; j<argc; j++) {
	    rb_ary_push(tmp, rb_ary_elt(argv[j], i));
	}
	if (NIL_P(result)) {
	    rb_yield(tmp);
	}
	else {
	    rb_ary_push(result, tmp);
	}
    }
    return result;
}
{% endhighlight %}

*Rubinius*

{% highlight ruby %}
def zip(*others)
  out = Array.new(size) { [] }
  others = others.map { |ary| ary.to_ary }

  size.times do |i|
    slot = out.at(i)
    slot << @tuple.at(@start + i)
    others.each { |ary| slot << ary.at(i) }
  end

  if block_given?
    out.each { |ary| yield ary }
    return nil
  end

  out
end
{% endhighlight %}


The zip method is somewhat obscure, I haven't seen it used very often. Still, it is good to look overview what options ruby provides.