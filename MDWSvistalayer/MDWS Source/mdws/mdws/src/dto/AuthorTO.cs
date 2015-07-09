using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class AuthorTO : AbstractTO
    {
        public String id = "";
        public String name = "";
        public String signature = "";

        public AuthorTO() { }

        public AuthorTO(Author mdoAuthor)
        {
            if (mdoAuthor == null)
            {
                return;
            }
            this.id = mdoAuthor.Id;
            this.name = mdoAuthor.Name;
            this.signature = mdoAuthor.Signature;
        }

        public AuthorTO(String id, String name, String signature)
        {
            this.id = id;
            this.name = name;
            this.signature = signature;
        }
    }
}
