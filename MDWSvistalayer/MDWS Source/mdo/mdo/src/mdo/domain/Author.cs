using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Author
    {
        String id;
        String name;
        String signature;

        public Author() { }

        public Author(String id, String name, String signature)
        {
            Id = id;
            Name = name;
            Signature = signature;
        }

        public String Id
        {
            get { return id; }
            set { id = value; }
        }

        public String Name
        {
            get { return name; }
            set { name = value; }
        }

        public String Signature
        {
            get { return signature; }
            set { signature = value; }
        }
    }
}
