using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using System.Xml.Serialization;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedText : AbstractTO
    {
        public string tag;
        public string text;
        public string[] textArray;
        public TaggedText[] taggedResults;

        public TaggedText() { }

        public TaggedText(string tag)
        {
            this.tag = tag;
        }

        public TaggedText(string tag, string text)
        {
            this.tag = tag;
            this.text = text;
        }

        public TaggedText(KeyValuePair<string, string> kvp)
        {
            this.tag = kvp.Key;
            this.text = kvp.Value;
        }

        public TaggedText(KeyValuePair<int, string> kvp)
        {
            this.tag = Convert.ToString(kvp.Key);
            this.text = kvp.Value;
        }

        public TaggedText(DictionaryEntry de)
        {
            this.tag = (string)de.Key;
            this.text = (string)de.Value;
        }

        public TaggedText(string tag, string[] textArray)
        {
            this.tag = tag;
            this.textArray = textArray;
        }

        public TaggedText(KeyValuePair<string, string[]> kvp)
        {
            this.tag = kvp.Key;
            this.textArray = kvp.Value;
        }

        public TaggedText(KeyValuePair<string, ArrayList> kvp)
        {
            this.tag = kvp.Key;
            this.textArray = (string[])kvp.Value.ToArray(typeof(string));
        }

        public TaggedText(string tag, TaggedText[] taggedResults)
        {
            this.tag = tag;
            this.taggedResults = taggedResults;
        }

        public TaggedText(string tag, StringDictionary dict)
        {
            this.tag = tag;
            this.taggedResults = new TaggedText[dict.Count];
            int i = 0;
            foreach (DictionaryEntry de in dict)
            {
                this.taggedResults[i++] = new TaggedText(de);
            }
        }

        public TaggedText(string tag, OrderedDictionary dict)
        {
            this.tag = tag;
            this.taggedResults = new TaggedText[dict.Count];
            int i = 0;
            foreach (DictionaryEntry de in dict)
            {
                this.taggedResults[i++] = new TaggedText(de);
            }
        }

        public TaggedText(string tag, DictionaryHashList dict)
        {
            this.tag = tag;
            this.taggedResults = new TaggedText[dict.Count];
            for (int i=0; i<dict.Count; i++)
            {
                this.taggedResults[i] = new TaggedText(dict[i]);
            }
        }
    }
}
