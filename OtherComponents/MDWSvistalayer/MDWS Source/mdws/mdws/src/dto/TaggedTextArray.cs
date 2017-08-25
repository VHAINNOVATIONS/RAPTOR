using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Text;
using gov.va.medora.mdo;
using System.IO;
using System.Xml.Serialization;
using gov.va.medora.mdo.domain.ccr;

namespace gov.va.medora.mdws.dto
{
    public class TaggedTextArray : AbstractArrayTO
    {
        public TaggedText[] results;
        private bool textOnly;

        public TaggedTextArray() { }

        public TaggedTextArray(IndexedHashtable t)
        {
            this.textOnly = false;
            setProps(t);
        }

        public TaggedTextArray(IndexedHashtable t, bool textOnly)
        {
            this.textOnly = textOnly;
            setProps(t); 
        }

        public TaggedTextArray(OrderedDictionary d)
        {
            this.textOnly = false;
            if (d == null || d.Count == 0)
            {
                return;
            }
            this.results = new TaggedText[d.Count];
            int i = 0;
            foreach (DictionaryEntry de in d)
            {
                this.results[i++] = new TaggedText(de);
            }
        }

        private void setProps(IndexedHashtable t)
        {
            this.results = new TaggedText[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                this.results[i] = new TaggedText();
                this.results[i].tag = (string)t.GetKey(i);
                if (t.GetValue(i) == null)
                {
                    continue;
                }
                Type vType = t.GetValue(i).GetType();
                if (vType == typeof(string))
                {
                    this.results[i].text = (string)t.GetValue(i);
                }
                else if (vType == typeof(string[]))
                {
                    string[] a = (string[])t.GetValue(i);
                    this.results[i].taggedResults = new TaggedText[a.Length];
                    for (int j = 0; j < a.Length; j++)
                    {
                        if (textOnly)
                        {
                            this.results[i].taggedResults[j] = new TaggedText("", a[j]);
                        }
                        else
                        {
                            this.results[i].taggedResults[j] = new TaggedText(a[j]);
                        }
                    }
                }
                else if (vType == typeof(Dictionary<string, ArrayList>))
                {
                    Dictionary<string, ArrayList> d = (Dictionary<string, ArrayList>)t.GetValue(i);
                    this.results[i].taggedResults = new TaggedText[d.Count];
                    int j = 0;
                    foreach (KeyValuePair<string, ArrayList> kvp in d)
                    {
                        this.results[i].taggedResults[j++] = new TaggedText(kvp);
                    }
                }
                else if (vType == typeof(IndexedHashtable))
                {
                    IndexedHashtable tbl = (IndexedHashtable)t.GetValue(i);
                    this.results[i].taggedResults = new TaggedText[tbl.Count];
                    for (int j = 0; j < tbl.Count; j++)
                    {
                        this.results[i].taggedResults[j] = new TaggedText((string)tbl.GetKey(j),(string)tbl.GetValue(j));
                    }
                }
                else if (vType == typeof(DictionaryHashList))
                {
                    DictionaryHashList d = (DictionaryHashList)t.GetValue(i);
                    this.results[i].taggedResults = new TaggedText[d.Count];
                    for (int j = 0; j < d.Count; j++)
                    {
                        this.results[i].taggedResults[j] = new TaggedText(d[j]);
                    }
                }
                else if (vType == typeof(KeyValuePair<int, string>))
                {
                    if (t.Count == 1)
                    {
                        this.results = new TaggedText[] { new TaggedText((KeyValuePair<int, string>)t.GetValue(i)) };
                    }
                    else
                    {
                        this.results[i].taggedResults = new TaggedText[] { new TaggedText((KeyValuePair<int, string>)t.GetValue(i)) };
                    }
                }
                else if (vType == typeof(DateTime))
                {
                    string s = ((DateTime)t.GetValue(i)).ToString("yyyyMMdd.HHmmss");
                    if (t.Count == 1)
                    {
                        this.results = new TaggedText[] { new TaggedText(t.GetKey(i).ToString(), s) };
                    }
                    else
                    {
                        this.results[i].taggedResults = new TaggedText[] { new TaggedText(t.GetKey(i).ToString(), s) };
                    }
                }
                else if (vType == typeof(StringDictionary))
                {
                    StringDictionary sd = (StringDictionary)t.GetValue(i);
                    this.results[i] = new TaggedText(this.results[i].tag, sd);
                }
                else if (vType == typeof(OrderedDictionary))
                {
                    OrderedDictionary d = (OrderedDictionary)t.GetValue(i);
                    this.results[i] = new TaggedText(this.results[i].tag, d);
                }
                else if (vType == typeof(User))
                {
                    string s = ((User)t.GetValue(i)).Uid;
                    this.results[i].text = s;
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    this.results[i].fault = new FaultTO((Exception)t.GetValue(i));
                }
                else if (vType == typeof(gov.va.medora.mdo.domain.ccr.ContinuityOfCareRecord))
                {
                    // serialize CCR as XML
                    MemoryStream memStream = new MemoryStream();
                    XmlSerializer serializer = new XmlSerializer(typeof(ContinuityOfCareRecord));
                    serializer.Serialize(memStream, new ContinuityOfCareRecord());
                    this.results[i] = new TaggedText(this.results[i].tag, "<![CDATA[" + System.Text.Encoding.UTF8.GetString(memStream.ToArray()) + "]]>");
                }
            }
            this.count = t.Count;
        }

        public TaggedTextArray(StringDictionary d)
        {
            if (d == null)
            {
                this.count = 0;
                return;
            }
            this.results = new TaggedText[d.Count];
            int i = 0;
            foreach (DictionaryEntry de in d)
            {
                this.results[i++] = new TaggedText(de);
            }
            this.count = d.Count;
        }

        public TaggedTextArray(Dictionary<string, string> d)
        {
            if (d == null)
            {
                this.count = 0;
                return;
            }

            this.count = d.Count;
            this.results = new TaggedText[this.count];
            int index = 0;
            foreach(KeyValuePair<string, string> kvp in d)
            {
                this.results[index++] = new TaggedText(kvp);
            }
        }
    }
}
