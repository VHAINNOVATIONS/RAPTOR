using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedNoteArrays : AbstractArrayTO
    {
        public TaggedNoteArray[] arrays;

        public TaggedNoteArrays() { }

        /// <summary>
        /// Use this contructor for results from the VPR so only the correct note type is built for a request. "documents" for all notes, "dischargeSummaries" for that specific note type
        /// </summary>
        /// <param name="t"></param>
        /// <param name="noteType"></param>
        public TaggedNoteArrays(IndexedHashtable t, string noteType)
        {
            if (t == null || t.Count == 0)
            {
                return;
            }
            count = t.Count;
            arrays = new TaggedNoteArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedNoteArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedNoteArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    if (noteType.Equals("documents"))
                    {
                        buildAllNotes(t, i);
                    }
                    else if (noteType.Equals("dischargeSummaries"))
                    {
                        buildDischargeSummaries(t, i);
                    }
                }
            }

        }

        public TaggedNoteArrays(IndexedHashtable t)
        {
            if (t == null || t.Count == 0)
            {
                return;
            }
            arrays = new TaggedNoteArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedNoteArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedNoteArray((string)t.GetKey(i),(Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    IList<Note> temp = ((System.Collections.Hashtable)t.GetValue(i))["documents"] as IList<Note>;
                    if (temp == null || temp.Count == 0)
                    {
                        arrays[i] = new TaggedNoteArray((string)t.GetKey(i));
                    }
                    else
                    {
                        Note[] ary = new Note[temp.Count];
                        temp.CopyTo(ary, 0);
                        arrays[i] = new TaggedNoteArray((string)t.GetKey(i), ary);
                    }
                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedNoteArray((string)t.GetKey(i), (Note[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedNoteArray((string)t.GetKey(i), (Note)t.GetValue(i));
                }
            }
            count = t.Count;
        }

        internal void add(string siteId, IList<Note> notes)
        {
            if (arrays == null || arrays.Length == 0)
            {
                arrays = new TaggedNoteArray[1];
            }
            else
            {
                Array.Resize<TaggedNoteArray>(ref arrays, arrays.Length + 1);
            }

            arrays[arrays.Length - 1] = new TaggedNoteArray(siteId, ((List<Note>)notes).ToArray());
        }

        internal void buildDischargeSummaries(IndexedHashtable ihs, int index)
        {
            IList<Note> temp = ((System.Collections.Hashtable)ihs.GetValue(index))["dischargeSummaries"] as IList<Note>;
            if (temp == null || temp.Count == 0)
            {
                arrays[index] = new TaggedNoteArray((string)ihs.GetKey(index));
            }
            else
            {
                Note[] ary = new Note[temp.Count];
                temp.CopyTo(ary, 0);
                arrays[index] = new TaggedNoteArray((string)ihs.GetKey(index), ary);
            }
        }

        internal void buildAllNotes(IndexedHashtable ihs, int index)
        {
            IList<Note> temp = ((System.Collections.Hashtable)ihs.GetValue(index))["documents"] as IList<Note>;
            if (temp == null || temp.Count == 0)
            {
                arrays[index] = new TaggedNoteArray((string)ihs.GetKey(index));
            }
            else
            {
                Note[] ary = new Note[temp.Count];
                temp.CopyTo(ary, 0);
                arrays[index] = new TaggedNoteArray((string)ihs.GetKey(index), ary);
            }
        }
    }
}
