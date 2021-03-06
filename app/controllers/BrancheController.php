<?php

class BrancheController extends BaseController {

	protected $branch;

	public function __construct(Branche $branch)
	{
		$this->branch = $branch;
	}

	public function getIndex()
	{
		$title = "Sucursales";

        $branches = Branche::all();

		return View::make('branches.index')
				->with(compact('branches', 'title'));
	}

	public function getAdd()
	{
		$title = "Nueva sucursal";

		return View::make('branches.add')
				->with('title', $title);
	}

	public function postAdd()
	{
    	$title = 'Nueva sucursal';

		if(Auth::user() && (Auth::user()->permitido('administrador'))) {
			$input = Input::all();

			$v = Validator::make($input, Branche::$rules, Branche::$messages);

	        if ($v->passes())
	        {
	        	try {
	            $this->branch->create($input);

	        	} catch (Exception $e) {
	        		// $message = $e->getMessage();
	        		$message = 'No se ha podido guardar la nueva sucursal, quizá exista otra con ese nombre.';
	        		Session::flash('message', $message);

	        		return Redirect::to('branches/add')
        			->withInput();
	        	}

	            return Redirect::to('branches/index');
	        }

	        return Redirect::to('branches/add')
	            ->withInput()
	            ->withErrors($v)
	            ->with('message');
		}
	}

	public function getEdit($id)
    {
    	$title = 'Editar Sucursal';

		if(Auth::user() && (Auth::user()->permitido('administrador'))) {

	        $branch = $this->branch->find($id);

	        if (is_null($branch))
	        {
	            return Redirect::to('branches/index');
	        }

	        return View::make('branches.edit')
	        	->with(compact('title', 'branch'));
	    }
    }

	public function postUpdate()
    {
    	$title = 'Editar Sucursal';
        $input = array_except(Input::all(), '_method');
        $id = $input['id'];

        $v = Validator::make($input, Branche::$rules, Branche::$messages);

        if ($v->passes())
        {
            $branch = $this->branch->find($id);

        	try {
            	$branch->update($input);

        	} catch (Exception $e) {
        		// $message = $e->getMessage();
        		$message = 'No se han guardado cambios porque hay otra sucursal con ese nombre.';
        		Session::flash('message', $message);

        		return View::make('branches.edit')
		            ->with(compact('title', 'branch'));
        	}

            return Redirect::to('branches/index');
        }

        return Redirect::to('branches/edit/'. $id)
            ->withInput()
            ->withErrors($v)
            ->with('message', 'Hay errores de validación.');
    }

    public function getJson()
    {
        $branches = Branche::all();

        return View::make('branches.json')
                ->with(compact('branches'));
    }

    public function getSelect()
    {
        $title = "Sucursales";

        $branches = Branche::all();

        return View::make('branches.select')
                ->with(compact('branches', 'title'));
    }

    public function getExcelByBranch($idBranch)
    {
        $branch = Branche::find($idBranch);

        if(empty($branch))
        {
            return Redirect::to('branches');
        }

        /** Error reporting */
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('America/Bogota');

        if (PHP_SAPI == 'cli')
            die('Este archivo corre únicamente desde un navegador web.');

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator(Auth::user()->name)
                                     ->setLastModifiedBy(Auth::user()->name)
                                     ->setTitle("Informe de artículos")
                                     ->setSubject("Sucursal ". $branch->name)
                                     ->setDescription("Este documento contiene la lista de artículos de la sucursal ". $branch->name)
                                     ->setKeywords("artículos, sucursal, ". $branch->name)
                                     ->setCategory("Archivo generado");


        // Datos de sucursal
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Código de sucursal')
                    ->setCellValue('B1', $branch->id)
                    ->setCellValue('A2', 'Nombre de sucursal')
                    ->setCellValue('B2', $branch->name);

        // Encabezados con UTF-8
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A3', 'Código de artículo')
                    ->setCellValue('B3', 'Nombre de artículo')
                    ->setCellValue('C3', 'Stock')
                    ->setCellValue('D3', 'Unidad de medida')
                    ->setCellValue('E3', 'Precio unitario')
                    ->setCellValue('F3', 'Costo unitario')
                    ->setCellValue('G3', 'Precio neto')
                    ->setCellValue('H3', 'Costo neto');

        $stocks = Stock::where('branch_id', '=', $branch->id)->get();
        $fila = 4;
        foreach ($stocks as $stock) {
            // $article = Article::find($stock->article->id);

            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A'. $fila, $stock->article->id)
                    ->setCellValue('B'. $fila, $stock->article->name)
                    ->setCellValue('C'. $fila, $stock->stock)
                    ->setCellValue('D'. $fila, $stock->article->unit)
                    ->setCellValue('E'. $fila, $stock->article->price)
                    ->setCellValue('F'. $fila, $stock->article->cost)
                    ->setCellValue('G'. $fila, ($stock->article->price * $stock->stock))
                    ->setCellValue('H'. $fila, ($stock->article->cost * $stock->stock));
            $fila++;
        }


        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($branch->name . date('Y-m-d'));


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $branch->name . date('_Y-m-d_His') .'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    } #getExcelByBranch

}
