using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class CptCodeTO
    {
        public string id;
        public string name;

        public CptCodeTO() { }

        public CptCodeTO(CptCode cpt)
        {
            if (cpt == null)
            {
                return;
            }

            id = cpt.Id;
            name = cpt.Name;
        }
    }
}
