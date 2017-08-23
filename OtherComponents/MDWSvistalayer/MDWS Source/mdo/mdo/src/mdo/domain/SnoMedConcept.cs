using System;
using System.Collections.Generic;
using System.Text;
using System.Collections;

namespace gov.va.medora.mdo
{
    public class SnoMedConcept
    {
        string id;
        SnoMedDescription fsn;
        SnoMedDescription preferredTerm;
        ArrayList synonyms;
        ArrayList attributes;

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public SnoMedDescription FSN
        {
            get { return fsn; }
            set { fsn = value; }
        }

        public SnoMedDescription PreferredTerm
        {
            get { return preferredTerm; }
            set { preferredTerm = value; }
        }

        public string SemanticTag
        {
            get
            {
                if (preferredTerm == null || preferredTerm.Term == null)
                {
                    return "";
                }
                int i = preferredTerm.Term.IndexOf('(');
                return preferredTerm.Term.Substring(i);
            }
        }

        public SnoMedDescription[] Synonyms
        {
            get { return (SnoMedDescription[])synonyms.ToArray(typeof(SnoMedDescription)); }
            set
            {
                synonyms = new ArrayList();
                for (int i = 0; i < ((SnoMedDescription[])value).Length; i++)
                {
                    synonyms.Add(((SnoMedDescription[])value)[i]);
                }
            }
        }

        public void addSynonym(SnoMedDescription synonym)
        {
            synonyms.Add(synonym);
        }

        public bool HasSynonyms
        {
            get{return synonyms.Count > 0;}
        }

        public SnoMedAttribute[] Attributes
        {
            get { return (SnoMedAttribute[])attributes.ToArray(typeof(SnoMedAttribute)); }
            set
            {
                attributes = new ArrayList();
                for (int i = 0; i < ((SnoMedAttribute[])value).Length; i++)
                {
                    attributes.Add(((SnoMedAttribute[])value)[i]);
                }
            }
        }

        public void addAttribute(SnoMedAttribute attribute)
        {
            attributes.Add(attribute);
        }

        public bool HasAttributes
        {
            get { return attributes.Count > 0; }
        }

        public SnoMedAttribute getAttribute(string attributeName)
        {
            if (attributes == null)
            {
                return null;
            }
            for (int i = 0; i < attributes.Count; i++)
            {
                if (((SnoMedAttribute)attributes[i]).Name == attributeName)
                {
                    return (SnoMedAttribute)attributes[i];
                }
            }
            return null;
        }
    }
}
