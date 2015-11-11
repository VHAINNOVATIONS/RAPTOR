using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class GeographicLocationArray : AbstractArrayTO
    {
        public GeographicLocationTO[] locations;

        public GeographicLocationArray() { }

        public GeographicLocationArray(GeographicLocation[] mdo)
        {
            if (mdo == null || mdo.Length == 0)
            {
                count = 0;
                return;
            }
            locations = new GeographicLocationTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                locations[i] = new GeographicLocationTO(mdo[i]);
            }
            count = mdo.Length;
        }
    }
}
