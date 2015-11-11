using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.dto.vista.mgt
{
    [Serializable]
    public class XRefArray : AbstractArrayTO
    {
        public XRefTO[] xrefs;

        public XRefArray() { }

        public XRefArray(IList<gov.va.medora.mdo.dao.vista.CrossRef> mdos)
        {
            if (mdos == null || mdos.Count <= 0)
            {
                return;
            }

            this.count = mdos.Count;
            xrefs = new XRefTO[mdos.Count];
            for (int i = 0; i < mdos.Count; i++)
            {
                xrefs[i] = new XRefTO(mdos[i]);
            }
        }

        public XRefArray(Dictionary<string, CrossRef> mdos)
        {
            if (mdos == null || mdos.Count <= 0)
            {
                return;
            }

            this.count = mdos.Count;
            xrefs = new XRefTO[mdos.Count];
            IList<XRefTO> xrefList = new List<XRefTO>(mdos.Count);
            foreach (CrossRef xref in mdos.Values)
            {
                xrefList.Add(new XRefTO(xref));
            }
            xrefList.CopyTo(xrefs, 0);
        }
    }
}