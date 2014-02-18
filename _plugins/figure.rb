# Generate a figure such as:
# <figure>
#   <img src="/media/2014/02/16/parallel.jpg"/>
#   <figcaption>Path planning is hard</figcaption>
# </figure>

module Jekyll
  class FigureTag < Liquid::Tag

    def initialize(tag_name, text, tokens)
      super
      @src, _, @caption = text.partition(" ")
      @caption.chomp!(" ")
    end

    def render(context)
      "<figure><img src=\"#{@src}\" alt=\"#{@caption}\" title=\"#{@caption}\"/><figcaption>#{@caption}</figcaption></figure>"
    end
  end
end

Liquid::Template.register_tag('figure', Jekyll::FigureTag)
