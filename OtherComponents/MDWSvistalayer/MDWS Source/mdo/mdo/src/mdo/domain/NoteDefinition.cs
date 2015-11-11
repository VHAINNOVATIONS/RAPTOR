using System;
using System.Collections.Generic;
using System.Collections;
using System.Text;

namespace gov.va.medora.mdo
{
    public class NoteDefinition
    {
        string id;
        ArrayList localTitles;
        string standardTitle;
        string type;
        string vuid;

        public NoteDefinition() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string[] LocalTitles
        {
            get { return (string[])localTitles.ToArray(typeof(string)); }
            set
            {
                localTitles = new ArrayList();
                for (int i = 0; i < ((string[])value).Length; i++)
                {
                    localTitles.Add(((string[])value)[i]);
                }
            }
        }

        public void addLocalTitle(string localTitle)
        {
            if (localTitles == null)
            {
                localTitles = new ArrayList();
            }
            localTitles.Add(localTitle);
        }

        public string StandardTitle
        {
            get { return standardTitle; }
            set { standardTitle = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public string Vuid
        {
            get { return vuid; }
            set { vuid = value; }
        }
    }
}
