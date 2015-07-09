using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class SnoMedAttribute
    {
        string name;
        ArrayList concepts;

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public SnoMedConcept[] Concepts
        {
            get { return (SnoMedConcept[])concepts.ToArray(typeof(SnoMedConcept)); }
            set
            {
                concepts = new ArrayList();
                for (int i = 0; i < ((SnoMedConcept[])value).Length; i++)
                {
                    concepts.Add(((SnoMedConcept[])value)[i]);
                }
            }
        }

        public void addConcept(SnoMedConcept concept)
        {
            concepts.Add(concept);
        }

        public bool HasConcepts
        {
            get { return concepts.Count > 0; }
        }
    }
}
