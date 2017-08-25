using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using InterSystems.Data.CacheClient;
using InterSystems.Data.CacheTypes;

namespace gov.va.medora.mdo.dao.cache
{
    public class CacheDao
    {
        CacheConnection _cxn;

        public CacheDao(AbstractConnection cxn)
        {
            _cxn = (CacheConnection)cxn;
        }

    }
}
