using System;
using System.Collections.Generic;
using System.Collections;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PersonArray : AbstractArrayTO
    {
        public PersonTO[] persons;

        public PersonArray() { }

        public PersonArray(Person[] mdo)
        {
            setProps(mdo);
        }

        public PersonArray(ArrayList lst)
        {
            setProps((Person[])lst.ToArray(typeof(Person)));    
        }

        public PersonArray(IList<Person> mdos)
        {
            if (mdos == null || mdos.Count <= 0)
            {
                return;
            }
            Person[] ary = new Person[mdos.Count];
            mdos.CopyTo(ary, 0);
            setProps(ary);
        }

        private void setProps(Person[] mdo)
        {
            if (mdo == null)
            {
                return;
            }
            persons = new PersonTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                persons[i] = new PersonTO(mdo[i]);
            }
            count = mdo.Length;
        }

        public PersonArray(SortedList lst)
        {
            if (lst == null || lst.Count == 0)
            {
                count = 0;
                return;
            }
            persons = new PersonTO[lst.Count];
            IDictionaryEnumerator e = lst.GetEnumerator();
            int i = 0;
            while (e.MoveNext())
            {
                persons[i++] = new PersonTO((Person)e.Value);
            }
            count = lst.Count;
        }

        public PersonArray(IndexedHashtable t)
        {
            if (t == null || t.Count == 0)
            {
                count = 0;
                return;
            }
            persons = new PersonTO[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) != null)
                {
                    persons[i] = new PersonTO((Person)t.GetValue(i));
                }
            }
            count = t.Count;
        }
    }
}