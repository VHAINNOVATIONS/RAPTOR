using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedPersonArray : AbstractTaggedArrayTO
    {
        public string sourceName;
        public PersonTO[] persons;

        public TaggedPersonArray() { }

        public TaggedPersonArray(string tag, Person[] persons)
        {
            this.tag = tag;
            setSourceName();
            if (persons == null)
            {
                this.count = 0;
                return;
            }
            this.persons = new PersonTO[persons.Length];
            for (int i = 0; i < persons.Length; i++)
            {
                this.persons[i] = new PersonTO(persons[i]);
            }
            this.count = persons.Length;
        }

        public TaggedPersonArray(string tag, List<Person> persons)
        {
            this.tag = tag;
            setSourceName();
            if (persons == null)
            {
                this.count = 0;
                return;
            }
            this.persons = new PersonTO[persons.Count];
            for (int i = 0; i < persons.Count; i++)
            {
                this.persons[i] = new PersonTO(persons[i]);
            }
            this.count = persons.Count;
        }

        public TaggedPersonArray(string tag, Person person)
        {
            this.tag = tag;
            setSourceName();
            if (person == null)
            {
                this.count = 0;
                return;
            }
            this.persons = new PersonTO[1];
            this.persons[0] = new PersonTO(person);
            this.count = 1;
        }

        public TaggedPersonArray(string tag)
        {
            this.tag = tag;
            setSourceName();
            this.count = 0;
        }

        public TaggedPersonArray(string tag, Exception e)
        {
            this.tag = tag;
            setSourceName();
            this.fault = new FaultTO(e);
        }


        internal void setSourceName()
        {
            if (tag == "ADR")
            {
                this.sourceName = "Administrative Data Repository";
            }
            else if (tag == "VADIR")
            {
                this.sourceName = "VA-DoD Information Repository";
            }
            else if (tag == "VBACORP")
            {
                this.sourceName = "VBA Corp";
            }
            else
            {
                this.sourceName = "National Patient Table";
            }
        }
    }
}
