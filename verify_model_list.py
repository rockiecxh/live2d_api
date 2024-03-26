import fnmatch
import os
import json

pattern = "*.json"


def gen_model_list():
    models = {"models": [], "messages": []}
    root_path = os.getcwd()
    os.chdir("models")
    model_path = os.getcwd()
    list_models = os.listdir(model_path)

    model_map = {}
    model_id_name_map = {}
    for d in list_models:
        model_id = d.split('_')[0]
        spath = os.path.join(model_path, d)
        list_of_files = os.listdir(spath)
        has_model_json = False
        for entry in list_of_files:
            if fnmatch.fnmatch(entry, pattern):
                has_model_json = True
                model_file = os.path.join(spath, entry)
                with open(model_file, 'r') as f:
                    model_json = json.load(f)
                    model_file_name = model_json['model'].split('/')[-1]
                    chara_name = model_file_name.split('_')[0]
                    model_id_name_map[model_id] = chara_name
                break
        if has_model_json:
            if model_id not in model_map:
                model_map[model_id] = []
            model_map[model_id].append(d)

    for k, v in model_map.items():
        if len(v) == 1:
            models['models'].append(v[0])
        else:
            models['models'].append(v)
        models['messages'].append(model_id_name_map[k])

    print(len(models['models']), len(models['messages']))
    with open(os.path.join(root_path, 'model_list_new.json'), 'w') as f:
        f.write(json.dumps(models))


def verify_model_list():
    root_path = os.getcwd()
    model_list_file = os.path.join(root_path, 'model_list.json')
    with open(model_list_file, 'r', encoding='utf-8') as f:
        j = json.load(f)
        print(len(j['models']), len(j['messages']))


if __name__ == '__main__':
    verify_model_list()
