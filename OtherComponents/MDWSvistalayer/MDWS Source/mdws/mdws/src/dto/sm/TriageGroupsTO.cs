using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class TriageGroupsTO : AbstractTO
    {
        public int count;
        public TriageGroupTO[] triageGroups;

        public TriageGroupsTO() { }

        public TriageGroupsTO(IList<gov.va.medora.mdo.domain.sm.TriageGroup> groups)
        {
            if (groups == null || groups.Count <= 0)
            {
                return;
            }

            count = groups.Count;
            triageGroups = new TriageGroupTO[count];

            for (int i = 0; i < count; i++)
            {
                triageGroups[i] = new TriageGroupTO(groups[i]);
            }
        }
    }
}